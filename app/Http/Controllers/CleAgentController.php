<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResoutAgentCourant;
use App\Models\Agent;
use App\Models\CleAgent;
use App\Models\Noeud;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CleAgentController extends Controller
{
    use ResoutAgentCourant;

    public function __construct(private AuditService $audit)
    {
    }

    /** Clés publiques valides d'un agent. */
    public function index(Request $request, Agent $agent): JsonResponse
    {
        $this->agentCourant($request);

        abort_unless($agent->estActif(), 404);

        return response()->json(
            $agent->cles()->valides()->get(['id', 'agent_id', 'usage', 'cle_publique', 'empreinte', 'expire_le'])
        );
    }

    /**
     * Enregistrement d'une clé publique : réservé à l'administrateur du nœud,
     * après vérification en personne de l'empreinte avec l'agent.
     * (Une clé ajoutée par un attaquant lui permettrait de lire les messages de l'agent.)
     */
    public function store(Request $request, Agent $agent): JsonResponse
    {
        $admin = $this->agentCourant($request);
        Gate::authorize('administrer');

        abort_unless($agent->etat_id === $this->etatLocalId(), 403, 'Cet agent dépend d\'un autre État.');
        abort_unless($agent->estActif(), 422, 'Agent inactif.');

        $data = $request->validate([
            'usage'        => ['required', Rule::in(['signature', 'chiffrement'])],
            'cle_publique' => ['required', 'string', 'max:512'],
            'expire_le'    => ['nullable', 'date', 'after:now'],
        ]);

        // Ed25519 (signature) et X25519 (chiffrement) : clés publiques de 32 octets.
        // À adapter si vous changez d'algorithme.
        $brut = base64_decode($data['cle_publique'], true);
        abort_if($brut === false || strlen($brut) !== 32, 422, 'Clé publique invalide (32 octets en base64 attendus).');

        // L'empreinte est calculée par le serveur, jamais fournie par le client.
        $empreinte = hash('sha256', $brut);
        abort_if(CleAgent::where('empreinte', $empreinte)->exists(), 422, 'Cette clé est déjà enregistrée.');

        $cle = DB::transaction(function () use ($agent, $data, $empreinte, $admin, $request) {
            $cle = CleAgent::create([
                'agent_id'     => $agent->id,
                'usage'        => $data['usage'],
                'cle_publique' => $data['cle_publique'],
                'empreinte'    => $empreinte,
                'expire_le'    => $data['expire_le'] ?? null,
            ]);

            $this->audit->enregistrer('enregistrement_cle', $admin, $cle, $request->ip());

            return $cle;
        });

        return response()->json($cle->only(['id', 'usage', 'empreinte', 'expire_le']), 201);
    }

    /** Révocation : par l'administrateur (agents de son État) ou par l'agent propriétaire. */
    public function revoquer(Request $request, CleAgent $cle): JsonResponse
    {
        $acteur = $this->agentCourant($request);

        $estProprietaire = $acteur->id === $cle->agent_id;
        $estAdminLocal = Gate::allows('administrer')
            && $cle->agent->etat_id === $this->etatLocalId();

        abort_unless($estProprietaire || $estAdminLocal, 403);
        abort_if($cle->estRevoquee(), 409, 'Clé déjà révoquée.');

        $data = $request->validate(['motif' => ['nullable', 'string', 'max:255']]);

        DB::transaction(function () use ($cle, $data, $acteur, $request) {
            $cle->update([
                'revoquee_le'      => now(),
                'motif_revocation' => $data['motif'] ?? null,
            ]);

            $this->audit->enregistrer('revocation_cle', $acteur, $cle, $request->ip());
        });

        return response()->json(['revoquee_le' => $cle->revoquee_le]);
    }

    private function etatLocalId(): int
    {
        return (int) Noeud::findOrFail(config('echange.noeud_id'))->etat_id;
    }
}