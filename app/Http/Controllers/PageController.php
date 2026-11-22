<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResoutAgentCourant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PageController extends Controller
{
    use ResoutAgentCourant;

    /** Page unique « Échanges sécurisés » : les données arrivent ensuite par l'API. */
    public function __invoke(Request $request): View
    {
        $agent = $this->agentCourant($request)->load('etat');

        return view('echange.index', [
            'agent' => $agent,
            'admin' => Gate::allows('administrer'),
        ]);
    }
}