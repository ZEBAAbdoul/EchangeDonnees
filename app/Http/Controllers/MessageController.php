<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EnregistreDestinataires;
use App\Http\Controllers\Concerns\ResoutAgentCourant;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    use ResoutAgentCourant, EnregistreDestinataires;

    public function __construct(private AuditService $audit)
    {
    }

    /**
     * Messages chiffrés d'une conversation. Chaque message est renvoyé avec
     * UNIQUEMENT la clé enveloppée destinée à l'agent connecté.
     */
    public function index(Request $request, Conversation $conversation): JsonResponse
    {
        $agent = $this->agentCourant($request);
        $this->verifierAcces($conversation, $agent);

        $messages = $conversation->messages()
            ->with([
                'expediteur:id,nom,fonction',
                'destinataires' => fn ($q) => $q->where('agent_id', $agent->id),
            ])
            ->paginate(50);

        return response()->json($messages);
    }

    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        $agent = $this->agentCourant($request);
        $this->verifierAcces($conversation, $agent);

        $data = $request->validate([
            'contenu_chiffre' => ['required', 'string', 'max:1000000'],
            'nonce'           => ['required', 'string', 'max:64'],
            'signature'       => ['required', 'string', 'max:2048'],
        ] + $this->reglesDestinataires());

        // Le message doit être déchiffrable par TOUS les participants (expéditeur compris).
        $attendus = $conversation->participants()->pluck('agents.id')->map(fn ($i) => (int) $i)->sort()->values()->all();
        $recus = collect($data['destinataires'])->pluck('agent_id')->map(fn ($i) => (int) $i)->sort()->values()->all();
        abort_unless($attendus === $recus, 422, 'Les destinataires doivent correspondre exactement aux participants de la conversation.');

        $message = DB::transaction(function () use ($data, $conversation, $agent, $request) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'expediteur_id'   => $agent->id,
                'contenu_chiffre' => $data['contenu_chiffre'],
                'nonce'           => $data['nonce'],
                'signature'       => $data['signature'],
                'envoye_le'       => now(),
            ]);

            $this->enregistrerDestinataires($message, $data['destinataires'], $conversation->classification);
            $conversation->touch();
            $this->audit->enregistrer('envoi_message', $agent, $message, $request->ip());

            return $message;
        });

        return response()->json(['id' => $message->id, 'envoye_le' => $message->envoye_le], 201);
    }

    public function marquerLu(Request $request, Message $message): Response
    {
        $agent = $this->agentCourant($request);
        $this->verifierAcces($message->conversation, $agent);

        $destinataire = $message->destinataires()->where('agent_id', $agent->id)->firstOrFail();

        if ($destinataire->lu_le === null) {
            DB::transaction(function () use ($destinataire, $message, $agent, $request) {
                $destinataire->marquerCommeLu();
                $this->audit->enregistrer('lecture_message', $agent, $message, $request->ip());
            });
        }

        return response()->noContent();
    }

    private function verifierAcces(Conversation $conversation, $agent): void
    {
        abort_unless($conversation->aPourParticipant($agent), 403);
        abort_unless($agent->peutAcceder($conversation->classification), 403);
    }
}