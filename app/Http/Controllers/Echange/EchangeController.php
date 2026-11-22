<?php

namespace App\Http\Controllers\Echange;

use App\Http\Controllers\Concerns\ResoutAgentCourant;
use App\Http\Controllers\Controller;
use App\Models\JournalAudit;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Page « Échanges sécurisés » (vue echange.index).
 * Les opérations passent par l'API : AgentController, ConversationController, MessageController.
 */
class EchangeController extends Controller
{
    use ResoutAgentCourant;

    public function __invoke(Request $request): View
    {
        // 401 si non connecté, 403 si le compte n'est pas rattaché à un agent actif
        $agent = $this->agentCourant($request)->loadMissing('organisme.etat');

        JournalAudit::noter($agent, 'page.ouverte');

        return view('echange.index', [
            'agent' => $agent,
            'admin' => $agent->estAdmin(),
        ]);
    }
}