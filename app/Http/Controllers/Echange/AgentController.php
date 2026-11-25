<?php

namespace App\Http\Controllers\Echange;

use App\Http\Controllers\Concerns\ResoutAgentCourant;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\JournalAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentController extends Controller
{
    use ResoutAgentCourant;

    /** Agent connecté (avec sa clé publique active). */
    public function moi(Request $request): JsonResponse
    {
        $agent = $this->agentCourant($request)->load('organisme.etat', 'cleActive');

        // Le navigateur refuse de republier une clé révoquée (voir publierCleSiNecessaire dans echange.js)
        return response()->json(array_merge($agent->toArray(), [
            'cles_revoquees' => $agent->cles()->whereNotNull('revoquee_le')->pluck('cle_chiffrement')->all(),
        ]));
    }

    /** Annuaire : nécessaire pour chiffrer vers un destinataire. */
    public function annuaire(Request $request): JsonResponse
    {
        $this->agentCourant($request);

        $liste = Agent::with(['organisme.etat', 'cleActive'])
            ->where('actif', true)
            ->orderBy('nom')
            ->get()
            ->map(fn (Agent $a) => [
                'id'              => $a->id,
                'nom'             => $a->nom,
                'fonction'        => $a->fonction,
                'organisme'       => $a->organisme->sigle,
                'etat'            => $a->organisme->etat->code,
                'est_admin'       => (bool) $a->est_admin,
                'cle_chiffrement' => $a->cleActive?->cle_chiffrement,
                'cle_signature'   => $a->cleActive?->cle_signature,
            ]);

        return response()->json($liste);
    }

    /** Enregistre une nouvelle clé publique (l'ancienne est révoquée mais conservée). */
    public function enregistrerCle(Request $request): JsonResponse
    {
        $agent = $this->agentCourant($request);

        $data = $request->validate([
            'cle_chiffrement' => ['required', 'string', 'size:44'],   // 32 octets en base64
            'cle_signature'   => ['required', 'string', 'size:44'],
        ]);

        DB::transaction(function () use ($agent, $data) {
            $agent->cles()->whereNull('revoquee_le')->update(['revoquee_le' => now()]);
            $cle = $agent->cles()->create($data);
            JournalAudit::noter($agent, 'cle.enregistree', $cle);
        });

        return response()->json(['message' => 'Clé enregistrée.'], 201);
    }
}