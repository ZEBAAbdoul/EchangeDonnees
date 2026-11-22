<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EnregistreDestinataires;
use App\Http\Controllers\Concerns\ResoutAgentCourant;
use App\Models\Agent;
use App\Models\Destinataire;
use App\Models\Document;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    use ResoutAgentCourant, EnregistreDestinataires;

    public function __construct(private AuditService $audit)
    {
    }

    /** Documents reçus par l'agent connecté. */
    public function index(Request $request): JsonResponse
    {
        $agent = $this->agentCourant($request);

        $documents = Document::whereHas('destinataires', fn ($q) => $q->where('agent_id', $agent->id))
            ->with('expediteur:id,nom,fonction')
            ->latest()
            ->paginate(30);

        return response()->json($documents);
    }

    /**
     * Dépôt d'un fichier DÉJÀ CHIFFRÉ côté client (multipart/form-data).
     * Le serveur ne voit jamais le contenu ni le vrai nom du fichier.
     */
    public function store(Request $request): JsonResponse
    {
        $agent = $this->agentCourant($request);

        $data = $request->validate([
            'fichier'        => ['required', 'file', 'max:' . config('echange.taille_max_document_ko')],
            'classification' => ['required', Rule::in(['confidentiel', 'secret'])],
            'nom_chiffre'    => ['required', 'string', 'max:2048'],
            'hash_contenu'   => ['required', 'string', 'size:64'],
            'nonce'          => ['required', 'string', 'max:64'],
            'signature'      => ['required', 'string', 'max:2048'],
        ] + $this->reglesDestinataires());

        abort_unless($agent->peutAcceder($data['classification']), 403, 'Habilitation insuffisante.');

        $fichier = $request->file('fichier');

        // Intégrité du transfert : hash SHA-256 du fichier chiffré.
        abort_unless(
            hash_equals(strtolower($data['hash_contenu']), hash_file('sha256', $fichier->getRealPath())),
            422,
            'Le hash du fichier ne correspond pas.'
        );

        $disque = Storage::disk(config('echange.disque_documents'));
        $chemin = $disque->putFile('chiffres', $fichier); // nom aléatoire, jamais le nom d'origine

        try {
            $document = DB::transaction(function () use ($data, $agent, $fichier, $chemin, $request) {
                $document = Document::create([
                    'classification'  => $data['classification'],
                    'expediteur_id'   => $agent->id,
                    'nom_chiffre'     => $data['nom_chiffre'],
                    'chemin_stockage' => $chemin,
                    'taille'          => $fichier->getSize(),
                    'hash_contenu'    => strtolower($data['hash_contenu']),
                    'nonce'           => $data['nonce'],
                    'signature'       => $data['signature'],
                ]);

                $this->enregistrerDestinataires($document, $data['destinataires'], $data['classification']);
                $this->audit->enregistrer('depot_document', $agent, $document, $request->ip());

                return $document;
            });
        } catch (\Throwable $e) {
            $disque->delete($chemin); // pas de fichier orphelin si la transaction échoue
            throw $e;
        }

        return response()->json(['uuid' => $document->uuid], 201);
    }

    /** Métadonnées + clé enveloppée de l'agent connecté. */
    public function show(Request $request, Document $document): JsonResponse
    {
        $agent = $this->agentCourant($request);
        $destinataire = $this->destinataireAutorise($document, $agent);

        return response()->json([
            'document'       => $document->load('expediteur:id,nom,fonction'),
            'cle_enveloppee' => $destinataire->cle_enveloppee,
            'cle_agent_id'   => $destinataire->cle_agent_id,
        ]);
    }

    /** Téléchargement du fichier chiffré (le client le déchiffre localement). */
    public function download(Request $request, Document $document): StreamedResponse
    {
        $agent = $this->agentCourant($request);
        $destinataire = $this->destinataireAutorise($document, $agent);

        DB::transaction(function () use ($destinataire, $document, $agent, $request) {
            $destinataire->marquerCommeLu();
            $this->audit->enregistrer('telechargement_document', $agent, $document, $request->ip());
        });

        return Storage::disk(config('echange.disque_documents'))
            ->download($document->chemin_stockage, $document->uuid . '.chiffre');
    }

    private function destinataireAutorise(Document $document, Agent $agent): Destinataire
    {
        abort_unless($agent->peutAcceder($document->classification), 403);

        return $document->destinataires()->where('agent_id', $agent->id)->first()
            ?? abort(403);
    }
}