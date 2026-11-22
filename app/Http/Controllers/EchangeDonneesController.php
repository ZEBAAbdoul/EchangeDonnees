<?php

namespace App\Http\Controllers\Echange;

use App\Http\Controllers\Concerns\ResoutAgentCourant;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\EchangeDonnees;
use App\Models\JournalAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Échange de données structurées (alertes, personnes ou véhicules recherchés, incidents, demandes).
 * Le contenu est chiffré de bout en bout : le serveur gère uniquement les accès et les statuts.
 */
class EchangeDonneesController extends Controller
{
    use ResoutAgentCourant;

    /** Disque privé (storage/app) : jamais accessible directement depuis le web. */
    private const DISQUE = 'local';
    private const DOSSIER_PHOTOS = 'echange/photos';

    /** Taille maximale de la photo chiffrée, en Ko (le navigateur la réduit à environ 1 Mo avant chiffrement). */
    private const MAX_PHOTO_KO = 5120;

    /** Transitions de statut autorisées pour un destinataire. */
    private const TRANSITIONS = [
        'nouveau' => ['lu', 'traite', 'rejete'],
        'lu'      => ['traite', 'rejete'],
    ];

    /** Échanges envoyés ou reçus par l'agent connecté (200 derniers). */
    public function index(Request $request): JsonResponse
    {
        $agent = $this->agentCourant($request);

        $echanges = EchangeDonnees::query()
            ->whereHas('destinataires', fn ($q) => $q->where('agent_id', $agent->id))
            ->with(['expediteur:id,nom', 'destinataires.agent:id,nom'])
            ->orderByDesc('created_at')
            ->limit(200)
            ->get()
            ->map(function (EchangeDonnees $e) use ($agent) {
                $envoye = $e->expediteur_id === $agent->id;
                $mien = $e->destinataires->firstWhere('agent_id', $agent->id);
                $autres = $e->destinataires->where('agent_id', '!=', $e->expediteur_id);

                return [
                    'id'               => $e->id,
                    'type'             => $e->type,
                    'niveau'           => $e->niveau,
                    'priorite'         => $e->priorite,
                    'expediteur'       => $e->expediteur,
                    'sens'             => $envoye ? 'envoye' : 'recu',
                    'statut'           => $mien?->statut,
                    'contenu_chiffre'  => $e->contenu_chiffre,
                    'nonce'            => $e->nonce,
                    'signature'        => $e->signature,
                    'ma_cle'           => $mien?->cle_chiffree,
                    'a_photo'          => $e->photo_chemin !== null,
                    'photo_header'     => $e->photo_header,
                    'photo_hash'       => $e->photo_hash,
                    'photo_taille'     => $e->photo_taille,
                    'created_at'       => $e->created_at,
                    'nb_destinataires' => $autres->count(),
                    // L'expéditeur suit l'avancement ; un destinataire ne voit pas les autres
                    'destinataires'    => $envoye
                        ? $autres->map(fn ($d) => [
                            'agent'     => ['id' => $d->agent_id, 'nom' => $d->agent?->nom],
                            'statut'    => $d->statut,
                            'lu_le'     => $d->lu_le,
                            'traite_le' => $d->traite_le,
                        ])->values()
                        : [],
                ];
            });

        return response()->json($echanges);
    }

    /** Reçoit un échange déjà chiffré et une enveloppe de clé par destinataire. */
    public function store(Request $request): JsonResponse
    {
        $agent = $this->agentCourant($request);

        // Avec une photo, la requête est un envoi de fichier (multipart) : la liste des destinataires arrive alors en JSON texte
        if (is_string($request->input('destinataires'))) {
            $decode = json_decode($request->input('destinataires'), true);
            $request->merge(['destinataires' => is_array($decode) ? $decode : []]);
        }

        $data = $request->validate([
            'type'                     => ['required', Rule::in(EchangeDonnees::TYPES)],
            'niveau'                   => ['required', 'integer', 'between:1,4'],
            'priorite'                 => ['required', Rule::in(EchangeDonnees::PRIORITES)],
            'contenu_chiffre'          => ['required', 'string', 'max:90000'],
            'nonce'                    => ['required', 'string', 'max:64'],
            'signature'                => ['required', 'string', 'max:128'],
            'destinataires'            => ['required', 'array', 'min:1', 'max:100'],
            'destinataires.*.agent_id' => ['required', 'uuid', 'distinct'],
            'destinataires.*.cle'      => ['required', 'string', 'max:512'],
            'photo'                    => ['nullable', 'file', 'max:' . self::MAX_PHOTO_KO],
            'photo_header'             => ['required_with:photo', 'nullable', 'string', 'max:64'],
            'photo_hash'               => ['required_with:photo', 'nullable', 'string', 'max:64'],
        ]);

        $fichierPhoto = $request->file('photo');
        abort_if(
            $fichierPhoto && ! in_array($data['type'], EchangeDonnees::TYPES_AVEC_PHOTO, true),
            422,
            'Ce type d\'échange n\'accepte pas de photo.'
        );

        abort_if(
            ! $fichierPhoto && $request->filled('photo_hash'),
            422,
            "La photo n'est pas arrivée sur le serveur : vérifiez upload_max_filesize et post_max_size dans php.ini."
        );

        // L'empreinte annoncée (et signée par l'expéditeur) doit être celle du fichier reçu
        if ($fichierPhoto && function_exists('sodium_crypto_generichash')) {
            $attendu = base64_decode($data['photo_hash'], true);
            $reel = sodium_crypto_generichash((string) file_get_contents($fichierPhoto->getRealPath()), '', 32);
            abort_unless($attendu !== false && hash_equals($attendu, $reel), 422, "L'empreinte de la photo ne correspond pas au fichier reçu.");
        }

        $liste = collect($data['destinataires']);
        $ids = $liste->pluck('agent_id');

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
        $cheminPhoto = $fichierPhoto
            ? $fichierPhoto->storeAs(self::DOSSIER_PHOTOS, $id . '.bin', self::DISQUE)
            : null;
        abort_if($fichierPhoto && ! $cheminPhoto, 500, "Enregistrement de la photo impossible sur le serveur.");

        try {
            $echange = DB::transaction(function () use ($agent, $data, $liste, $id, $cheminPhoto, $fichierPhoto) {
                $e = new EchangeDonnees();
                $e->id = $id;
                $e->fill([
                    'expediteur_id'   => $agent->id,
                    'type'            => $data['type'],
                    'niveau'          => $data['niveau'],
                    'priorite'        => $data['priorite'],
                    'contenu_chiffre' => $data['contenu_chiffre'],
                    'nonce'           => $data['nonce'],
                    'signature'       => $data['signature'],
                ]);
                // forceFill : indépendant de la propriété $fillable du modèle (un modèle pas à jour ignorerait ces champs en silence)
                $e->forceFill([
                    'photo_chemin' => $cheminPhoto,
                    'photo_header' => $cheminPhoto ? $data['photo_header'] : null,
                    'photo_hash'   => $cheminPhoto ? $data['photo_hash'] : null,
                    'photo_taille' => $cheminPhoto ? $fichierPhoto->getSize() : null,
                ]);
                $e->save();

                foreach ($liste as $d) {
                    $e->destinataires()->create([
                        'agent_id'     => $d['agent_id'],
                        'cle_chiffree' => $d['cle'],
                        'statut'       => $d['agent_id'] === $agent->id ? 'emis' : 'nouveau',
                    ]);
                }

                JournalAudit::noter($agent, 'donnees.envoyees', $e);

                return $e;
            });
        } catch (\Throwable $erreur) {
            if ($cheminPhoto) {
                Storage::disk(self::DISQUE)->delete($cheminPhoto);   // pas de fichier orphelin
            }
            throw $erreur;
        }

        return response()->json([
            'id'         => $echange->id,
            'created_at' => $echange->created_at,
            'a_photo'    => $echange->photo_chemin !== null,   // permet au navigateur de vérifier que la photo est bien arrivée
        ], 201);
    }

    /** Envoie la photo chiffrée, uniquement aux participants de l'échange (expéditeur compris). */
    public function photo(Request $request, EchangeDonnees $echange): StreamedResponse
    {
        $agent = $this->agentCourant($request);

        abort_unless(
            $echange->destinataires()->where('agent_id', $agent->id)->exists(),
            403,
            "Vous n'êtes pas destinataire de cet échange."
        );
        abort_unless(
            $echange->photo_chemin && Storage::disk(self::DISQUE)->exists($echange->photo_chemin),
            404,
            'Photo introuvable sur le serveur.'
        );

        JournalAudit::noter($agent, 'donnees.photo', $echange);

        return Storage::disk(self::DISQUE)->download($echange->photo_chemin, 'photo.bin', [
            'Content-Type'  => 'application/octet-stream',
            'Cache-Control' => 'no-store',
        ]);
    }

    /** Un destinataire marque l'échange comme lu, traité ou rejeté. */
    public function statut(Request $request, EchangeDonnees $echange): JsonResponse
    {
        $agent = $this->agentCourant($request);

        $data = $request->validate([
            'statut' => ['required', Rule::in(['lu', 'traite', 'rejete'])],
        ]);

        $ligne = $echange->destinataires()->where('agent_id', $agent->id)->first();
        abort_unless($ligne, 403, "Vous n'êtes pas destinataire de cet échange.");
        abort_if($ligne->statut === 'emis', 403, "L'expéditeur ne peut pas modifier le statut de son propre envoi.");

        $autorises = self::TRANSITIONS[$ligne->statut] ?? [];
        abort_unless(in_array($data['statut'], $autorises, true), 422, 'Changement de statut impossible depuis « ' . $ligne->statut . ' ».');

        $ligne->statut = $data['statut'];
        if ($data['statut'] === 'lu') {
            $ligne->lu_le = now();
        } else {
            $ligne->lu_le ??= now();
            $ligne->traite_le = now();
        }
        $ligne->save();

        JournalAudit::noter($agent, 'donnees.' . $data['statut'], $echange);

        return response()->json(['statut' => $ligne->statut, 'traite_le' => $ligne->traite_le]);
    }
}