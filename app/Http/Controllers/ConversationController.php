<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResoutAgentCourant;
use App\Models\Agent;
use App\Models\Conversation;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ConversationController extends Controller
{
    use ResoutAgentCourant;

    public function __construct(private AuditService $audit)
    {
    }

    /** Conversations dont l'agent connecté est participant. */
    public function index(Request $request): JsonResponse
    {
        $agent = $this->agentCourant($request);

        $conversations = $agent->conversations()
            ->with('createur:id,nom,fonction')
            ->orderByDesc('conversations.updated_at')
            ->paginate(30);

        return response()->json($conversations);
    }

    public function store(Request $request): JsonResponse
    {
        $agent = $this->agentCourant($request);

        $data = $request->validate([
            'classification' => ['required', Rule::in(['confidentiel', 'secret'])],
            'sujet_chiffre'  => ['nullable', 'string', 'max:5000'],
            'participants'   => ['required', 'array', 'min:1', 'max:50'],
            'participants.*' => ['integer', 'distinct', 'exists:agents,id'],
        ]);

        abort_unless($agent->peutAcceder($data['classification']), 403, 'Habilitation insuffisante.');

        $ids = collect($data['participants'])->map(fn ($i) => (int) $i)->push($agent->id)->unique()->values();

        $participants = Agent::with('etat')->findMany($ids);
        foreach ($participants as $p) {
            abort_unless(
                $p->etat->estActif() && $p->peutAcceder($data['classification']),
                422,
                "Un participant n'est pas habilité pour ce niveau de classification."
            );
        }

        $conversation = DB::transaction(function () use ($data, $agent, $ids, $request) {
            $conversation = Conversation::create([
                'classification' => $data['classification'],
                'createur_id'    => $agent->id,
                'sujet_chiffre'  => $data['sujet_chiffre'] ?? null,
            ]);

            foreach ($ids as $id) {
                $conversation->participants()->attach($id, [
                    'role' => $id === $agent->id ? 'createur' : 'participant',
                ]);
            }

            $this->audit->enregistrer('creation_conversation', $agent, $conversation, $request->ip());

            return $conversation;
        });

        return response()->json($this->avecParticipants($conversation), 201);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $agent = $this->agentCourant($request);

        abort_unless($conversation->aPourParticipant($agent), 403);
        abort_unless($agent->peutAcceder($conversation->classification), 403);

        return response()->json($this->avecParticipants($conversation));
    }

    private function avecParticipants(Conversation $conversation): Conversation
    {
        return $conversation->load([
            'participants' => fn ($q) => $q->select('agents.id', 'agents.nom', 'agents.fonction', 'agents.structure', 'agents.etat_id'),
        ]);
    }
}