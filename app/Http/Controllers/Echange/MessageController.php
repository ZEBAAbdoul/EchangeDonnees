<?php

namespace App\Http\Controllers\Echange;

use App\Http\Controllers\Concerns\ResoutAgentCourant;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\JournalAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    use ResoutAgentCourant;

    /** Messages chiffrés d'une conversation (50 par page, du plus ancien au plus récent). */
    public function index(Request $request, Conversation $conversation): JsonResponse
    {
        $agent = $this->agentCourant($request);
        abort_unless($conversation->aMembre($agent), 403);

        $messages = $conversation->messages()
            ->with([
                'expediteur:id,nom',
                'cles' => fn ($q) => $q->where('agent_id', $agent->id),
            ])
            ->orderBy('created_at')
            ->paginate(50);

        // Chaque agent ne reçoit que SA clé chiffrée
        $messages->through(fn ($m) => [
            'id'              => $m->id,
            'expediteur'      => $m->expediteur,
            'niveau'          => $m->niveau,
            'contenu_chiffre' => $m->contenu_chiffre,
            'nonce'           => $m->nonce,
            'signature'       => $m->signature,
            'ma_cle'          => $m->cles->first()?->cle_chiffree,
            'created_at'      => $m->created_at,
        ]);

        return response()->json($messages);
    }

    /** Enregistre un message déjà chiffré par le navigateur de l'expéditeur. */
    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        $agent = $this->agentCourant($request);
        abort_unless($conversation->aMembre($agent), 403);

        $data = $request->validate([
            'niveau'          => ['required', 'integer', 'between:1,4'],
            'contenu_chiffre' => ['required', 'string', 'max:200000'],
            'nonce'           => ['required', 'string', 'max:64'],
            'signature'       => ['required', 'string', 'max:128'],
            'cles'            => ['required', 'array'],
            'cles.*.agent_id' => ['required', 'uuid'],
            'cles.*.cle'      => ['required', 'string', 'max:512'],
        ]);

        abort_if($data['niveau'] > $agent->habilitation, 403, 'Niveau supérieur à votre habilitation.');

        // Il faut une enveloppe pour chaque membre, ni plus ni moins
        $membres = $conversation->membres()->pluck('agents.id')->sort()->values();
        $fournis = collect($data['cles'])->pluck('agent_id')->unique()->sort()->values();
        abort_unless($membres->all() === $fournis->all(), 422, 'Une clé chiffrée est requise pour chaque membre.');

        $message = DB::transaction(function () use ($agent, $conversation, $data) {
            $m = $conversation->messages()->create([
                'expediteur_id'   => $agent->id,
                'niveau'          => $data['niveau'],
                'contenu_chiffre' => $data['contenu_chiffre'],
                'nonce'           => $data['nonce'],
                'signature'       => $data['signature'],
            ]);

            foreach ($data['cles'] as $c) {
                $m->cles()->create(['agent_id' => $c['agent_id'], 'cle_chiffree' => $c['cle']]);
            }

            $conversation->touch();
            JournalAudit::noter($agent, 'message.envoye', $m);

            return $m;
        });

        return response()->json(['id' => $message->id, 'created_at' => $message->created_at], 201);
    }
}