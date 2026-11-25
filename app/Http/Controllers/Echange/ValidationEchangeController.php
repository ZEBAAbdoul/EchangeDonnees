<?php

namespace App\Http\Controllers\Echange;

use App\Http\Controllers\Concerns\ResoutAgentCourant;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\EchangeDonnees;
use App\Models\JournalAudit;
use App\Services\ValidationEchange;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Validation des échanges destinés à un autre État : décision signée de chaque administrateur de l'État émetteur,
 * puis libération de la clé de lecture aux destinataires étrangers.
 */
class ValidationEchangeController extends Controller
{
    use ResoutAgentCourant;

    /** POST /donnees/{id}/validation : approbation ou refus signé d'un administrateur */
    public function decider(Request $request, string $id): JsonResponse
    {
        $agent = $this->agentCourant($request);
        $agent->loadMissing('organisme');

        $d = $request->validate([
            'decision'      => ['required', 'in:approuve,refuse'],
            'signature'     => ['required', 'string', 'max:200'],
            'motif_chiffre' => ['nullable', 'string', 'max:4000'],
            'motif_nonce'   => ['nullable', 'string', 'max:64'],
        ]);

        abort_unless($agent->actif && $agent->est_admin, 403, 'Réservé aux administrateurs actifs.');

        $cleSignature = $this->cleSignature($agent);
        abort_if($cleSignature === null, 422, "Aucune clé de signature active pour votre compte : publiez votre clé avant de valider.");

        EchangeDonnees::expirerEchus();

        return DB::transaction(function () use ($id, $agent, $d, $cleSignature) {
            $e = EchangeDonnees::lockForUpdate()->findOrFail($id);

            abort_unless($e->validation === 'en_attente', 422, "Cet échange n'attend plus de validation.");
            abort_unless($agent->organisme?->etat_id === $e->etat_emetteur_id, 403, "Vous n'appartenez pas à l'État émetteur.");

            $v = $e->validations()->where('valideur_id', $agent->id)->lockForUpdate()->first();
            abort_unless($v, 403, "Vous n'êtes pas administrateur valideur de cet échange.");
            abort_unless($v->decision === 'en_attente', 422, 'Vous avez déjà donné votre décision.');

            $refus = $d['decision'] === 'refuse';
            abort_if($refus && (empty($d['motif_chiffre']) || empty($d['motif_nonce'])), 422, 'Le motif du refus est obligatoire.');

            $message = ValidationEchange::messageDecision(
                $e->id, $d['decision'], $e->signature, $d['motif_nonce'] ?? null, $d['motif_chiffre'] ?? null
            );
            abort_unless(
                ValidationEchange::signatureValide($d['signature'], $message, $cleSignature),
                422,
                'Signature de la décision invalide.'
            );

            $v->update([
                'decision'      => $d['decision'],
                'signature'     => $d['signature'],
                'motif_chiffre' => $refus ? $d['motif_chiffre'] : null,
                'motif_nonce'   => $refus ? $d['motif_nonce'] : null,
                'decide_le'     => now(),
            ]);

            // Règle stricte : TOUS les valideurs exigés comptent, même désactivés depuis l'envoi (aucune dispense)
            $e->validation = ValidationEchange::statutApresDecision($e->validations()->pluck('decision')->all());
            if ($e->validation === 'validee') {
                $e->valide_le = now();
            }
            $e->save();

            JournalAudit::noter($agent, 'donnees.validation_' . $d['decision'], $e);

            return response()->json(['validation' => $e->validation, 'a_liberer' => $e->validation === 'validee']);
        });
    }

    /** POST /donnees/{id}/liberer : remet la clé de lecture aux destinataires des autres États */
    public function liberer(Request $request, string $id): JsonResponse
    {
        $agent = $this->agentCourant($request);

        $data = $request->validate([
            'enveloppes'            => ['required', 'array', 'min:1', 'max:100'],
            'enveloppes.*.agent_id' => ['required', 'uuid'],
            'enveloppes.*.cle'      => ['required', 'string', 'max:512'],
        ]);

        return DB::transaction(function () use ($id, $agent, $data) {
            $e = EchangeDonnees::lockForUpdate()->findOrFail($id);

            abort_unless($e->validation === 'validee', 422, "Cet échange n'est pas en attente de libération.");

            $autorise = $e->expediteur_id === $agent->id
                || $e->validations()->where('valideur_id', $agent->id)->where('decision', 'approuve')->exists();
            abort_unless($autorise, 403, "Seuls l'expéditeur et les administrateurs ayant approuvé peuvent libérer cet échange.");

            $attente = $e->destinataires()->where('statut', 'attente')->pluck('agent_id')->all();
            abort_unless(
                ValidationEchange::liberationConforme($attente, array_column($data['enveloppes'], 'agent_id')),
                422,
                'Les enveloppes ne correspondent pas aux destinataires en attente.'
            );

            foreach ($data['enveloppes'] as $env) {
                $e->destinataires()->where('agent_id', $env['agent_id'])
                    ->update(['cle_chiffree' => $env['cle'], 'statut' => 'nouveau']);
            }

            $e->forceFill(['validation' => 'liberee', 'libere_le' => now(), 'libere_par' => $agent->id])->save();

            JournalAudit::noter($agent, 'donnees.liberee', $e);

            return response()->json(['validation' => 'liberee']);
        });
    }

    /** POST /donnees/{id}/annuler : l'expéditeur retire un échange non libéré (ex. valideur désactivé) */
    public function annuler(Request $request, string $id): JsonResponse
    {
        $agent = $this->agentCourant($request);

        return DB::transaction(function () use ($id, $agent) {
            $e = EchangeDonnees::lockForUpdate()->findOrFail($id);

            abort_unless($e->expediteur_id === $agent->id, 403, "Seul l'expéditeur peut annuler cet échange.");
            abort_unless($e->estAnnulable(), 422, 'Cet échange ne peut plus être annulé.');

            $e->forceFill(['validation' => 'annulee'])->save();

            JournalAudit::noter($agent, 'donnees.annulee', $e);

            return response()->json(['validation' => 'annulee']);
        });
    }

    /**
     * Clé publique de signature active de l'agent (base64), ou null.
     * ← SEUL endroit à adapter : reprends exactement la logique qui renvoie « cle_signature » dans ton endpoint /agents.
     */
    private function cleSignature(Agent $agent): ?string
    {
        $cle = $agent->cleActive;                       // relation vers la clé publique active
        $valeur = $cle?->cle_signature;

        return is_string($valeur) && $valeur !== '' ? $valeur : null;
    }
}