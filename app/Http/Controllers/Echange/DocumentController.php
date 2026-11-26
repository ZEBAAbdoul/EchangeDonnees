<?php

namespace App\Http\Controllers\Echange;

use App\Http\Controllers\Concerns\ResoutAgentCourant;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Document;
use App\Models\JournalAudit;
use App\Services\ValidationEchange;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Documents chiffrés de bout en bout. Le serveur ne reçoit qu'un fichier déjà chiffré, ses métadonnées chiffrées
 * et une enveloppe de clé par destinataire. Un envoi vers un autre État doit d'abord être approuvé par tous les
 * administrateurs actifs de l'État émetteur (voir ValidationDocumentController).
 */
class DocumentController extends Controller
{
    use ResoutAgentCourant;

    /** Disque privé (storage/app) : jamais accessible directement depuis le web. */
    private const DISQUE = 'local';
    private const DOSSIER = 'echange/documents';

    /** Taille maximale du fichier chiffré, en Ko. Garder cohérent avec APP.maxDocMo (20 Mo) et php.ini. */
    public const MAX_KO = 21504;

    /** Documents envoyés, reçus ou à valider par l'agent connecté (200 derniers). */
    public function index(Request $request): JsonResponse
    {
        $agent = $this->agentCourant($request);
        Document::expirerEchus();

        $documents = Document::query()
            // « attente » : destinataire d'un autre État, sans clé tant que le document n'est pas libéré → rien à voir
            ->whereHas('destinataires', fn ($q) => $q->where('agent_id', $agent->id)->where('statut', '!=', 'attente'))
            ->with(['expediteur:id,nom', 'destinataires.agent:id,nom', 'validations.valideur:id,nom'])
            ->orderByDesc('created_at')
            ->limit(200)
            ->get()
            ->map(function (Document $d) use ($agent) {
                $envoye = $d->expediteur_id === $agent->id;
                $mien = $d->destinataires->firstWhere('agent_id', $agent->id);
                $maVal = $d->validations->firstWhere('valideur_id', $agent->id);
                $autres = $d->destinataires->where('role', 'destinataire')->where('agent_id', '!=', $d->expediteur_id);

                return [
                    'id'            => $d->id,
                    'niveau'        => $d->niveau,
                    'expediteur'    => $d->expediteur,
                    'sens'          => $envoye ? 'envoye' : ($mien?->role === 'valideur' ? 'a_valider' : 'recu'),
                    'statut'        => $mien?->statut,
                    'validation'    => $d->validation,
                    'expire_le'     => $d->expire_le,
                    'ma_validation' => $maVal?->decision,
                    'taille'        => $d->taille,
                    'header'        => $d->header,
                    'meta_nonce'    => $d->meta_nonce,
                    'meta_chiffree' => $d->meta_chiffree,
                    'hash'          => $d->hash,
                    'signature'     => $d->signature,
                    'ma_cle'        => $mien?->cle_chiffree,
                    'created_at'    => $d->created_at,
                    // L'expéditeur et les valideurs suivent l'avancement ; un destinataire ne voit pas les autres
                    'destinataires' => ($envoye || $maVal)
                        ? $autres->map(fn ($x) => [
                            'agent'  => ['id' => $x->agent_id, 'nom' => $x->agent?->nom],
                            'statut' => $x->statut,
                        ])->values()
                        : [],
                    // Décisions signées : chaque destinataire peut vérifier lui-même les approbations
                    'validations'   => $d->validations->map(fn ($v) => [
                        'agent'         => ['id' => $v->valideur_id, 'nom' => $v->valideur?->nom],
                        'decision'      => $v->decision,
                        'signature'     => $v->signature,
                        'motif_chiffre' => $v->motif_chiffre,
                        'motif_nonce'   => $v->motif_nonce,
                        'decide_le'     => $v->decide_le,
                    ])->values(),
                ];
            });

        return response()->json($documents);
    }

    /** Reçoit un document déjà chiffré, une enveloppe de clé par destinataire et une par administrateur valideur. */
    public function store(Request $request): JsonResponse
    {
        $agent = $this->agentCourant($request);
        $agent->loadMissing('organisme');

        // Requête multipart : les listes arrivent en JSON texte
        foreach (['destinataires', 'valideurs'] as $champ) {
            if (is_string($request->input($champ))) {
                $decode = json_decode($request->input($champ), true);
                $request->merge([$champ => is_array($decode) ? $decode : []]);
            }
        }

        $data = $request->validate([
            'fichier'                  => ['required', 'file', 'max:' . self::MAX_KO],
            'niveau'                   => ['required', 'integer', 'between:1,4'],
            'header'                   => ['required', 'string', 'max:64'],
            'meta_nonce'               => ['required', 'string', 'max:64'],
            'meta_chiffree'            => ['required', 'string', 'max:8000'],
            'hash'                     => ['required', 'string', 'max:64'],
            'signature'                => ['required', 'string', 'max:128'],
            'destinataires'            => ['required', 'array', 'min:1', 'max:100'],
            'destinataires.*.agent_id' => ['required', 'uuid', 'distinct'],
            // Absente pour un destinataire d'un autre État : il la recevra à la libération
            'destinataires.*.cle'      => ['nullable', 'string', 'max:512'],
            'valideurs'                => ['nullable', 'array', 'max:50'],
            'valideurs.*.agent_id'     => ['required', 'uuid', 'distinct'],
            'valideurs.*.cle'          => ['required', 'string', 'max:512'],
        ]);

        $fichier = $request->file('fichier');

        // L'empreinte annoncée (et signée par l'expéditeur) doit être celle du fichier reçu
        $attendu = base64_decode($data['hash'], true);
        abort_unless(
            $attendu !== false && hash_equals($attendu, $this->empreinte($fichier->getRealPath())),
            422,
            "L'empreinte du fichier ne correspond pas au fichier reçu."
        );

        $liste = collect($data['destinataires']);
        $ids = $liste->pluck('agent_id');

        abort_unless($ids->contains($agent->id), 422, "L'expéditeur doit figurer parmi les destinataires.");
        abort_if($data['niveau'] > $agent->habilitation, 403, 'Niveau supérieur à votre habilitation.');

        $agents = Agent::with('organisme')->whereIn('id', $ids)->get();
        abort_unless($agents->count() === $ids->count(), 422, 'Destinataire inconnu.');
        abort_if($agents->contains(fn (Agent $a) => ! $a->actif), 422, 'Un destinataire est inactif.');
        abort_if(
            $agents->contains(fn (Agent $a) => $a->habilitation < $data['niveau']),
            403,
            "Un destinataire n'a pas l'habilitation requise pour ce niveau."
        );

        // Envoi vers un autre État ? Qui reçoit tout de suite, qui valide, qui attend (même règle que le navigateur)
        $admins = Agent::with('organisme')->where('est_admin', true)->where('actif', true)->get();
        $plan = ValidationEchange::planifier(
            $this->tab($agent),
            $agents->map(fn (Agent $a) => $this->tab($a))->all(),
            $admins->map(fn (Agent $a) => $this->tab($a))->all(),
            (int) $data['niveau']
        );
        abort_if($plan['erreur'] !== null, 422, (string) $plan['erreur']);

        $erreurEnveloppes = ValidationEchange::verifierEnveloppes(
            $plan,
            $agent->id,
            $liste->map(fn ($d) => ['agent_id' => $d['agent_id'], 'cle' => $d['cle'] ?? null])->all(),
            $data['valideurs'] ?? []
        );
        abort_if($erreurEnveloppes !== null, 422, (string) $erreurEnveloppes);

        $id = (string) Str::uuid();
        $chemin = $fichier->storeAs(self::DOSSIER, $id . '.bin', self::DISQUE);
        abort_if(! $chemin, 500, "Enregistrement du document impossible sur le serveur.");

        try {
            $document = DB::transaction(function () use ($agent, $data, $liste, $id, $chemin, $fichier, $plan) {
                $direct = $plan['liberer_direct'];

                $doc = new Document();
                $doc->id = $id;
                $doc->forceFill([
                    'expediteur_id'    => $agent->id,
                    'etat_emetteur_id' => $agent->organisme->etat_id,
                    'niveau'           => $data['niveau'],
                    'chemin'           => $chemin,
                    'taille'           => $fichier->getSize(),
                    'header'           => $data['header'],
                    'meta_nonce'       => $data['meta_nonce'],
                    'meta_chiffree'    => $data['meta_chiffree'],
                    'hash'             => $data['hash'],
                    'signature'        => $data['signature'],
                    'validation'       => $plan['validation'],
                    'expire_le'        => $plan['validation'] === 'en_attente' ? now()->addDays(ValidationEchange::DELAI_JOURS) : null,
                    'valide_le'        => $direct ? now() : null,
                    'libere_le'        => $direct ? now() : null,
                    'libere_par'       => $direct ? $agent->id : null,
                ])->save();

                foreach ($liste as $d) {
                    // Destinataire d'un autre État : aucune clé tant que les administrateurs n'ont pas tous approuvé
                    $attend = in_array($d['agent_id'], $plan['externes'], true) && ! $direct;
                    $this->ajouterLigne($doc, $d['agent_id'], 'destinataire', $attend ? null : ($d['cle'] ?? null), $attend ? 'attente' : 'nouveau');
                }

                foreach ($data['valideurs'] ?? [] as $v) {
                    // Un valideur déjà destinataire a déjà son enveloppe de clé
                    if (! $liste->contains('agent_id', $v['agent_id'])) {
                        $this->ajouterLigne($doc, $v['agent_id'], 'valideur', $v['cle'], 'valideur');
                    }
                    $doc->validations()->create(['valideur_id' => $v['agent_id'], 'decision' => 'en_attente']);
                }

                JournalAudit::noter($agent, 'document.depose', $doc);

                return $doc;
            });
        } catch (\Throwable $erreur) {
            Storage::disk(self::DISQUE)->delete($chemin);   // pas de fichier orphelin
            throw $erreur;
        }

        return response()->json([
            'id'         => $document->id,
            'validation' => $document->validation,
            'created_at' => $document->created_at,
        ], 201);
    }

    /** Envoie le fichier chiffré, uniquement aux participants disposant d'une clé (valideurs compris). */
    public function download(Request $request, Document $document): StreamedResponse
    {
        $agent = $this->agentCourant($request);

        $ligne = $document->destinataires()->where('agent_id', $agent->id)->first();
        abort_unless($ligne && $ligne->statut !== 'attente', 403, "Vous n'êtes pas destinataire de ce document.");
        abort_unless(
            $document->chemin && Storage::disk(self::DISQUE)->exists($document->chemin),
            404,
            'Fichier introuvable sur le serveur.'
        );

        if ($ligne->role === 'destinataire' && $ligne->telecharge_le === null) {
            $ligne->forceFill(['telecharge_le' => now()])->save();
        }

        JournalAudit::noter($agent, 'document.telecharge', $document);

        return Storage::disk(self::DISQUE)->download($document->chemin, 'document.bin', [
            'Content-Type'  => 'application/octet-stream',
            'Cache-Control' => 'no-store',
        ]);
    }

    /** Empreinte BLAKE2b-256 du fichier, calculée par blocs (pas de fichier entier en mémoire). */
    private function empreinte(string $chemin): string
    {
        $etat = sodium_crypto_generichash_init('', 32);
        $f = fopen($chemin, 'rb');
        while ($f && ! feof($f)) {
            $bloc = fread($f, 1048576);
            if ($bloc !== false && $bloc !== '') {
                sodium_crypto_generichash_update($etat, $bloc);
            }
        }
        if ($f) {
            fclose($f);
        }

        return sodium_crypto_generichash_final($etat, 32);
    }

    /** Forme attendue par ValidationEchange (l'État d'un agent est celui de son organisme). */
    private function tab(Agent $a): array
    {
        return [
            'id'           => $a->id,
            'etat_id'      => $a->organisme?->etat_id,
            'est_admin'    => (bool) $a->est_admin,
            'actif'        => (bool) $a->actif,
            'habilitation' => (int) $a->habilitation,
            'nom'          => $a->nom,
        ];
    }

    /** Ajoute une ligne destinataire/valideur (forceFill : ne dépend pas du $fillable du modèle de ligne). */
    private function ajouterLigne(Document $doc, string $agentId, string $role, ?string $cle, string $statut): void
    {
        $ligne = $doc->destinataires()->make();
        $ligne->forceFill([
            'agent_id'     => $agentId,
            'role'         => $role,
            'cle_chiffree' => $cle,
            'statut'       => $statut,
        ])->save();
    }
}