<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Agent;
use Illuminate\Http\Request;

trait ResoutAgentCourant
{
    protected function agentCourant(Request $request): Agent
    {
        $agent = $request->attributes->get('agent_courant');

        if (! $agent) {
            $utilisateur = $request->user();
            abort_unless($utilisateur, 401);

            $agent = Agent::where('user_id', $utilisateur->getAuthIdentifier())->first();

            abort_unless(
                $agent && $agent->estActif(),
                403,
                "Votre compte n'est pas rattaché à un agent habilité."
            );

            $request->attributes->set('agent_courant', $agent);
        }

        return $agent;
    }
}