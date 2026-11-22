<?php

namespace App\Http\Controllers\Echange;

use App\Http\Controllers\Concerns\ResoutAgentCourant;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Document;
use App\Models\JournalAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Documents chiffrés de bout en bout.
 * Le serveur reçoit un fichier DÉJÀ chiffré par le navigateur : il ne peut ni le lire,
 * ni connaître son nom. Il contrôle seulement qui a le droit de le télécharger.
 */
class DocumentController extends Controller
{
    use ResoutAgentCourant;

    /** Disque privé (storage/app) : jamais accessible directement depuis le web. */
    private const DISQUE = 'local';
    private const DOSSIER = 'echange/documents';

    /** Taille maximale du fichier chiffré, en Ko (20 Mo). Doit rester ≤ upload_max_filesize de php.ini. */
    private const MAX_KO = 20480;

    /** Documents dont l'agent connecté est destinataire (expéditeur compris). */
    public function index(Request $request): JsonResponse
    {
        $agent = $this->agentCourant($request);

        $documents = Document::query()
            ->whereHas('destinataires', fn ($q) => $q->where('agent_id', $agent->id))
            ->with([
                'expediteur:id,nom',
                'destinataires' => fn ($q) => $q->where('agent_id', $agent->id),
            ])
            ->withCount('destinataires')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get()
            ->map(fn (Document $d) => [
                'id'                    => $d->id,
                'expediteur'            => $d->expediteur,
                'niveau'                => $d->niveau,
                'taille'                => $d->taille,
                'header'                => $d->header,
                'meta_nonce'            => $d->meta_nonce,
                'meta_chiffree'         => $d->meta_chiffree,
                'hash'                  => $d->hash,
                'signature'             => $d->signature,
                'nb_destinataires'      => $d->destinataires_count,
                'ma_cle'                => $d->destinataires->first()?->cle_chiffree,
                'created_at'            => $d->created_at,
            ]);

        return response()->json($documents);
    }

    /** Reçoit un fichier chiffré, ses métadonnées chiffrées et une enveloppe de clé par destinataire. */
    public function store(Request $request): JsonResponse
    {
        $agent = $this->agentCourant($request);

        $data = $request->validate([
            'fichier'       => ['required', 'file', 'max:' . self::MAX_KO],
            'niveau'        => ['required', 'integer', 'between:1,4'],
            'header'        => ['required', 'string', 'max:64'],
            'meta_nonce'    => ['required', 'string', 'max:64'],
            'meta_chiffree' => ['required', 'string', 'max:4000'],
            'hash'          => ['required', 'string', 'max:64'],
            'signature'     => ['required', 'string', 'max:128'],
            'destinataires' => ['required', 'string', 'max:100000'],   // JSON : [{agent_id, cle}, ...]
        ]);

        $liste = json_decode($data['destinataires'], true);

        $validation = Validator::make(['d' => $liste], [
            'd'            => ['required', 'array', 'min:1', 'max:100'],
            'd.*.agent_id' => ['required', 'uuid', 'distinct'],
            'd.*.cle'      => ['required', 'string', 'max:512'],
        ]);
        abort_if($validation->fails(), 422, 'Liste de destinataires invalide.');

        $ids = collect($liste)->pluck('agent_id');

        abort_unless($ids->contains($agent->id), 422, "L'expéditeur doit figurer parmi les destinataires.");
        abort_if($data['niveau'] > $agent->habilitation, 403, 'Niveau supérieur à votre habilitation.');

        // Chaque destinataire doit exister, être actif et avoir l'habilitation requise
        $agents = Agent::whereIn('id', $ids)->get();
        abort_unless($agents->count() === $ids->count(), 422, 'Destinataire inconnu.');
        abort_if($agents->contains(fn (Agent $a) => ! $a->actif), 422, 'Un destinataire est inactif.');
        abort_if(
            $agents->contains(fn (Agent $a) => $a->habilitation < $data['niveau']),
            403,
            "Un destinataire n'a pas l'habilitation requise pour ce niveau."
        );

        $id = (string) Str::uuid();
        $chemin = $request->file('fichier')->storeAs(self::DOSSIER, $id . '.bin', self::DISQUE);
        abort_unless($chemin, 500, "Enregistrement du fichier impossible sur le serveur.");

        try {
            $document = DB::transaction(function () use ($agent, $data, $liste, $id, $chemin, $request) {
                $doc = new Document();
                $doc->id = $id;
                $doc->fill([
                    'expediteur_id' => $agent->id,
                    'niveau'        => $data['niveau'],
                    'chemin'        => $chemin,
                    'taille'        => $request->file('fichier')->getSize(),
                    'header'        => $data['header'],
                    'meta_nonce'    => $data['meta_nonce'],
                    'meta_chiffree' => $data['meta_chiffree'],
                    'hash'          => $data['hash'],
                    'signature'     => $data['signature'],
                ]);
                $doc->save();

                foreach ($liste as $d) {
                    $doc->destinataires()->create(['agent_id' => $d['agent_id'], 'cle_chiffree' => $d['cle']]);
                }

                JournalAudit::noter($agent, 'document.depose', $doc);

                return $doc;
            });
        } catch (\Throwable $e) {
            Storage::disk(self::DISQUE)->delete($chemin);   // pas de fichier orphelin
            throw $e;
        }

        return response()->json(['id' => $document->id, 'created_at' => $document->created_at], 201);
    }

    /** Envoie le fichier chiffré, uniquement aux destinataires. */
    public function download(Request $request, Document $document): StreamedResponse
    {
        $agent = $this->agentCourant($request);

        $destinataire = $document->destinataires()->where('agent_id', $agent->id)->first();
        abort_unless($destinataire, 403, "Vous n'êtes pas destinataire de ce document.");
        abort_unless(
            Storage::disk(self::DISQUE)->exists($document->chemin),
            404,
            'Fichier introuvable sur le serveur.'
        );

        $destinataire->forceFill(['telecharge_le' => now()])->save();
        JournalAudit::noter($agent, 'document.telecharge', $document);

        return Storage::disk(self::DISQUE)->download($document->chemin, 'document.bin', [
            'Content-Type'  => 'application/octet-stream',
            'Cache-Control' => 'no-store',
        ]);
    }
}
