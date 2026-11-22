<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResoutAgentCourant;
use App\Models\Agent;
use App\Models\Noeud;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    use ResoutAgentCourant;

    public function __construct(private AuditService $audit)
    {
    }

    /**
     * Annuaire : agents actifs des États actifs, avec leurs clés PUBLIQUES de
     * chiffrement valides (nécessaires à l'expéditeur pour envelopper la clé de contenu).
     * Le niveau d'habilitation n'est volontairement pas exposé.
     */
    public function index(Request $request): JsonResponse
    {
        $this->agentCourant($request);

        $agents = Agent::query()
            ->where('statut', 'actif')
            ->whereHas('etat', fn ($q) => $q->where('statut', 'actif'))
            ->with([
                'etat:id,code,nom',
                'cles' => fn ($q) => $q->valides()->usage('chiffrement')
                    ->select('id', 'agent_id', 'cle_publique', 'empreinte', 'expire_le'),
            ])
            ->select('id', 'etat_id', 'nom', 'fonction', 'structure')
            ->orderBy('nom')
            ->get();

        return response()->json($agents);
    }

    /** Création d'un agent : réservée à l'administrateur du nœud, pour son propre État. */
    public function store(Request $request): JsonResponse
    {
        $admin = $this->agentCourant($request);
        Gate::authorize('administrer');

        $data = $request->validate([
            'nom'                    => ['required', 'string', 'max:255'],
            'fonction'               => ['required', 'string', 'max:255'],
            'structure'              => ['nullable', 'string', 'max:255'],
            'niveau_habilitation'    => ['required', Rule::in(['confidentiel', 'secret'])],
            'habilitation_expire_le' => ['nullable', 'date', 'after:today'],
        ]);

        $agent = DB::transaction(function () use ($data, $admin, $request) {
            $agent = Agent::create($data + [
                'etat_id' => $this->etatLocalId(),
                'statut'  => 'actif',
            ]);

            $this->audit->enregistrer('creation_agent', $admin, $agent, $request->ip());

            return $agent;
        });

        return response()->json($agent, 201);
    }

    /** Révocation d'un agent et de toutes ses clés. */
    public function revoquer(Request $request, Agent $agent): JsonResponse
    {
        $admin = $this->agentCourant($request);
        Gate::authorize('administrer');

        abort_unless($agent->etat_id === $this->etatLocalId(), 403, 'Cet agent dépend d\'un autre État.');
        abort_if($agent->statut === 'revoque', 409, 'Agent déjà révoqué.');

        DB::transaction(function () use ($agent, $admin, $request) {
            $agent->update(['statut' => 'revoque']);

            $agent->cles()->whereNull('revoquee_le')->update([
                'revoquee_le'      => now(),
                'motif_revocation' => 'Agent révoqué',
            ]);

            $this->audit->enregistrer('revocation_agent', $admin, $agent, $request->ip());
        });

        return response()->json(['statut' => 'revoque']);
    }

    private function etatLocalId(): int
    {
        return (int) Noeud::findOrFail(config('echange.noeud_id'))->etat_id;
    }
}