<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Agent;
use App\Models\CleAgent;
use Illuminate\Database\Eloquent\Model;

trait EnregistreDestinataires
{
    /** Règles de validation à fusionner dans celles du contrôleur. */
    protected function reglesDestinataires(): array
    {
        return [
            'destinataires'                => ['required', 'array', 'min:1', 'max:50'],
            'destinataires.*.agent_id'     => ['required', 'integer', 'distinct', 'exists:agents,id'],
            'destinataires.*.cle_agent_id' => ['required', 'integer', 'exists:cles_agents,id'],
            'destinataires.*.cle_enveloppee' => ['required', 'string', 'max:4096'],
        ];
    }

    /**
     * Vérifie chaque destinataire (actif, habilité, clé de chiffrement valide)
     * puis enregistre la clé de contenu enveloppée pour lui.
     * À appeler dans une transaction.
     */
    protected function enregistrerDestinataires(Model $objet, array $destinataires, string $classification): void
    {
        $agents = Agent::with('etat')
            ->findMany(collect($destinataires)->pluck('agent_id'))
            ->keyBy('id');

        foreach ($destinataires as $d) {
            $agent = $agents->get((int) $d['agent_id']);

            abort_unless(
                $agent && $agent->etat->estActif() && $agent->peutAcceder($classification),
                422,
                "Un destinataire n'est pas habilité pour ce niveau de classification."
            );

            $cle = CleAgent::valides()
                ->usage('chiffrement')
                ->where('agent_id', $agent->id)
                ->whereKey($d['cle_agent_id'])
                ->first();

            abort_if($cle === null, 422, 'Clé de chiffrement invalide, expirée ou révoquée pour un destinataire.');

            $objet->destinataires()->create([
                'agent_id'       => $agent->id,
                'cle_agent_id'   => $cle->id,
                'cle_enveloppee' => $d['cle_enveloppee'],
            ]);
        }
    }
}