<?php

namespace App\Http\Controllers\Echange;

use App\Http\Controllers\Concerns\ResoutAgentCourant;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Document;
use App\Models\JournalAudit;
use App\Services\ValidationEchange;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Validation des documents destinés à un autre État : décision signée de chaque administrateur de l'État émetteur,
 * puis libération de la clé de lecture aux destinataires étrangers.
 */
class ValidationDocumentController extends Controller
{
    use ResoutAgentCourant;

    /** Domaine de signature : une approbation de document ne peut pas être rejouée sur un échange de données. */
    private const DOMAINE = 'validdoc';

    /** POST /documents/{id}/validation : approbation ou refus signé d'un administrateur */
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
        abort_if($cleSignature === null, 422, 'Aucune clé de signature active pour votre compte : publiez votre clé avant de valider.');

        Document::expirerEchus();

        return DB::transaction(function () use ($id, $agent, $d, $cleSignature) {
            $doc = Document::lockForUpdate()->findOrFail($id);

            abort_unless($doc->validation === 'en_attente', 422, "Ce document n'attend plus de validation.");
            abort_unless($agent->organisme?->etat_id === $doc->etat_emetteur_id, 403, "Vous n'appartenez pas à l'État émetteur.");

            $v = $doc->validations()->where('valideur_id', $agent->id)->lockForUpdate()->first();
            abort_unless($v, 403, "Vous n'êtes pas administrateur valideur de ce document.");
            abort_unless($v->decision === 'en_attente', 422, 'Vous avez déjà donné votre décision.');

            $refus = $d['decision'] === 'refuse';
            abort_if($refus && (empty($d['motif_chiffre']) || empty($d['motif_nonce'])), 422, 'Le motif du refus est obligatoire.');

            $message = ValidationEchange::messageDecision(
                $doc->id,
                $d['decision'],
                $doc->signature,
                $d['motif_nonce'] ?? null,
                $d['motif_chiffre'] ?? null,
                self::DOMAINE
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
            $doc->validation = ValidationEchange::statutApresDecision($doc->validations()->pluck('decision')->all());
            if ($doc->validation === 'validee') {
                $doc->valide_le = now();
            }
            $doc->save();

            JournalAudit::noter($agent, 'document.validation_' . $d['decision'], $doc);

            return response()->json(['validation' => $doc->validation, 'a_liberer' => $doc->validation === 'validee']);
        });
    }

    /** POST /documents/{id}/liberer : remet la clé de lecture aux destinataires des autres États */
    public function liberer(Request $request, string $id): JsonResponse
    {
        $agent = $this->agentCourant($request);

        $data = $request->validate([
            'enveloppes'            => ['required', 'array', 'min:1', 'max:100'],
            'enveloppes.*.agent_id' => ['required', 'uuid'],
            'enveloppes.*.cle'      => ['required', 'string', 'max:512'],
        ]);

        return DB::transaction(function () use ($id, $agent, $data) {
            $doc = Document::lockForUpdate()->findOrFail($id);

            abort_unless($doc->validation === 'validee', 422, "Ce document n'est pas en attente de libération.");

            $autorise = $doc->expediteur_id === $agent->id
                || $doc->validations()->where('valideur_id', $agent->id)->where('decision', 'approuve')->exists();
            abort_unless($autorise, 403, "Seuls l'expéditeur et les administrateurs ayant approuvé peuvent libérer ce document.");

            $attente = $doc->destinataires()->where('statut', 'attente')->pluck('agent_id')->all();
            abort_unless(
                ValidationEchange::liberationConforme($attente, array_column($data['enveloppes'], 'agent_id')),
                422,
                'Les enveloppes ne correspondent pas aux destinataires en attente.'
            );

            foreach ($data['enveloppes'] as $env) {
                $doc->destinataires()->where('agent_id', $env['agent_id'])
                    ->update(['cle_chiffree' => $env['cle'], 'statut' => 'nouveau']);
            }

            $doc->forceFill(['validation' => 'liberee', 'libere_le' => now(), 'libere_par' => $agent->id])->save();

            JournalAudit::noter($agent, 'document.liberee', $doc);

            return response()->json(['validation' => 'liberee']);
        });
    }

    /** POST /documents/{id}/annuler : l'expéditeur retire un document non libéré (ex. valideur désactivé) */
    public function annuler(Request $request, string $id): JsonResponse
    {
        $agent = $this->agentCourant($request);

        return DB::transaction(function () use ($id, $agent) {
            $doc = Document::lockForUpdate()->findOrFail($id);

            abort_unless($doc->expediteur_id === $agent->id, 403, "Seul l'expéditeur peut annuler ce document.");
            abort_unless($doc->estAnnulable(), 422, 'Ce document ne peut plus être annulé.');

            $doc->forceFill(['validation' => 'annulee'])->save();

            JournalAudit::noter($agent, 'document.annulee', $doc);

            return response()->json(['validation' => 'annulee']);
        });
    }

    /** Clé publique de signature active de l'agent (base64), ou null. */
    private function cleSignature(Agent $agent): ?string
    {
        $valeur = $agent->cleActive?->cle_signature;

        return is_string($valeur) && $valeur !== '' ? $valeur : null;
    }
}
