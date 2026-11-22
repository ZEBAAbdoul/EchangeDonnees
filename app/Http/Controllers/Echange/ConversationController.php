<?php

namespace App\Http\Controllers\Echange;

use App\Http\Controllers\Concerns\ResoutAgentCourant;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\JournalAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConversationController extends Controller
{
    use ResoutAgentCourant;

    /** Colonnes qualifiées : la table pivot conversation_membres a aussi une colonne « id ». */
    private const MEMBRES = 'membres:agents.id,agents.nom';

    /** Conversations de l'agent connecté, la plus récente d'abord. */
    public function index(Request $request): JsonResponse
    {
        $agent = $this->agentCourant($request);

        $conversations = $agent->conversations()
            ->with(self::MEMBRES)
            ->orderByDesc('conversations.updated_at')
            ->get();

        return response()->json($conversations);
    }

    /** Crée une conversation avec les agents choisis (l'auteur est ajouté automatiquement). */
    public function store(Request $request): JsonResponse
    {
        $agent = $this->agentCourant($request);

        $data = $request->validate([
            'titre'     => ['nullable', 'string', 'max:150'],
            'membres'   => ['required', 'array', 'min:1', 'max:50'],
            'membres.*' => ['uuid', 'exists:agents,id'],
        ]);

        $ids = collect($data['membres'])->push($agent->id)->unique()->values();

        $conversation = DB::transaction(function () use ($agent, $data, $ids) {
            $conversation = Conversation::create([
                'titre'      => $data['titre'] ?? null,
                'est_groupe' => $ids->count() > 2,
                'cree_par'   => $agent->id,
            ]);

            // La table pivot a une clé primaire UUID sans valeur par défaut : on la fournit
            $conversation->membres()->attach(
                $ids->mapWithKeys(fn ($id) => [$id => ['id' => (string) Str::uuid()]])->all()
            );

            JournalAudit::noter($agent, 'conversation.creee', $conversation);

            return $conversation;
        });

        return response()->json($conversation->load(self::MEMBRES), 201);
    }
}