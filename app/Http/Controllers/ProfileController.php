<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Agent;
use App\Models\Document;
use App\Models\DocumentDestinataire;
use App\Models\EchangeDonnees;
use App\Models\EchangeDonneesDestinataire;
use App\Models\JournalAudit;
use App\Models\Message;
use App\Models\Organisme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /* ------------------------------------------------------------------ */
    /*  Tableau de bord du SED                                             */
    /* ------------------------------------------------------------------ */

    public function dashboard(): View
    {
        return $this->tableauAdmin();
    }

    private function tableauAdmin(): View
    {
        $user = auth()->user();

        // rescue() : si les tables du module d'échanges n'existent pas encore,
        // le tableau de bord s'affiche quand même, sans chiffres.
        $agent = rescue(
            fn () => Agent::with(['organisme.etat', 'cleActive'])->where('user_id', $user->id)->first(),
            null,
            false
        );
        $agentActif = (bool) ($agent && $agent->actif);

        $stats = [];
        $graphique = $this->graphiqueVide();
        $derniersEchanges = collect();
        $activiteRecente = collect();
        $fileTraitement = collect();
        $repartitions = ['priorite' => [], 'niveau' => []];

        if ($agentActif) {
            $stats = rescue(fn () => $this->statistiquesAgent($agent), [], false);
            $graphique = rescue(fn () => $this->graphiqueActivite($agent), $this->graphiqueVide(), false);
            $derniersEchanges = rescue(fn () => $this->derniersEchanges($agent), collect(), false);
            $fileTraitement = rescue(fn () => $this->fileTraitement($agent), collect(), false);
            $repartitions = rescue(fn () => $this->repartitions($agent), $repartitions, false);
            $stats += rescue(fn () => ['plus_ancien' => $this->plusAncien($agent)], [], false);

            if ($agent->estAdmin()) {
                $stats += rescue(fn () => $this->statistiquesAdmin(), [], false);
                $activiteRecente = rescue(
                    fn () => JournalAudit::with('agent:id,nom')->orderByDesc('created_at')->limit(8)->get(),
                    collect(),
                    false
                );
            }
        }

        return view('dashboard', compact(
            'user', 'agent', 'agentActif', 'stats', 'graphique', 'derniersEchanges', 'activiteRecente',
            'fileTraitement', 'repartitions'
        ));
    }


    /** Échanges reçus en attente de traitement, les plus urgents puis les plus anciens d'abord (8 au plus). */
    private function fileTraitement(Agent $agent): Collection
    {
        $rang = ['urgente' => 0, 'haute' => 1, 'normale' => 2];

        return EchangeDonnees::query()
            ->whereHas('destinataires', fn ($q) => $q->where('agent_id', $agent->id)->whereIn('statut', ['nouveau', 'lu']))
            ->with([
                'expediteur:id,nom',
                'destinataires' => fn ($q) => $q->where('agent_id', $agent->id),
            ])
            ->get()
            ->sortBy(fn ($e) => [$rang[$e->priorite] ?? 3, $e->created_at?->timestamp ?? 0])
            ->take(8)
            ->values();
    }

    /** Date de réception du plus ancien échange encore à traiter (ou null). */
    private function plusAncien(Agent $agent): ?string
    {
        return EchangeDonnees::query()
            ->whereHas('destinataires', fn ($q) => $q->where('agent_id', $agent->id)->whereIn('statut', ['nouveau', 'lu']))
            ->min('created_at');
    }

    /** Répartition des échanges reçus par priorité et par niveau de classification. */
    private function repartitions(Agent $agent): array
    {
        $recus = fn () => EchangeDonnees::query()
            ->whereHas('destinataires', fn ($q) => $q->where('agent_id', $agent->id)->where('statut', '!=', 'emis'));

        return [
            'priorite' => $recus()->selectRaw('priorite, count(*) as n')->groupBy('priorite')->pluck('n', 'priorite')->all(),
            'niveau'   => $recus()->selectRaw('niveau, count(*) as n')->groupBy('niveau')->pluck('n', 'niveau')->all(),
        ];
    }

    /** Chiffres personnels de l'agent. */
    private function statistiquesAgent(Agent $agent): array
    {
        $conversations = $agent->conversations()->pluck('conversations.id');
        $messages = Message::whereIn('conversation_id', $conversations);

        $recus = EchangeDonneesDestinataire::where('agent_id', $agent->id)->where('statut', '!=', 'emis');
        $docsRecus = DocumentDestinataire::where('agent_id', $agent->id)
            ->whereHas('document', fn ($q) => $q->where('expediteur_id', '!=', $agent->id));

        return [
            'conversations'           => $conversations->count(),
            'messages_total'          => (clone $messages)->count(),
            'messages_recus_7j'       => (clone $messages)->where('expediteur_id', '!=', $agent->id)
                                            ->where('created_at', '>=', now()->subDays(7))->count(),

            'donnees_recues'          => (clone $recus)->count(),
            'donnees_nouvelles'       => (clone $recus)->where('statut', 'nouveau')->count(),
            'donnees_a_traiter'       => (clone $recus)->whereIn('statut', ['nouveau', 'lu'])->count(),
            'donnees_traitees'        => (clone $recus)->whereIn('statut', ['traite', 'rejete'])->count(),
            'donnees_urgentes'        => (clone $recus)->whereIn('statut', ['nouveau', 'lu'])
                                            ->whereHas('echange', fn ($q) => $q->where('priorite', 'urgente'))->count(),
            'donnees_envoyees'        => EchangeDonnees::where('expediteur_id', $agent->id)->count(),

            'documents_recus'         => (clone $docsRecus)->count(),
            'documents_a_telecharger' => (clone $docsRecus)->whereNull('telecharge_le')->count(),
            'documents_deposes'       => Document::where('expediteur_id', $agent->id)->count(),
        ];
    }

    /** Chiffres globaux, réservés aux administrateurs du module. */
    private function statistiquesAdmin(): array
    {
        return [
            'agents_actifs'   => Agent::where('actif', true)->count(),
            'agents_total'    => Agent::count(),
            'agents_sans_cle' => Agent::where('actif', true)
                                    ->whereDoesntHave('cles', fn ($q) => $q->whereNull('revoquee_le'))->count(),
            'organismes'      => Organisme::count(),
            'actions_24h'     => JournalAudit::where('created_at', '>=', now()->subDay())->count(),
        ];
    }

    /** Six derniers échanges de données (envoyés ou reçus), sans leur contenu (chiffré). */
    private function derniersEchanges(Agent $agent): Collection
    {
        return EchangeDonnees::query()
            ->whereHas('destinataires', fn ($q) => $q->where('agent_id', $agent->id))
            ->with([
                'expediteur:id,nom',
                'destinataires' => fn ($q) => $q->where('agent_id', $agent->id),
            ])
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();
    }

    /* ------------------------------------------------------------------ */
    /*  Graphique                                                          */
    /* ------------------------------------------------------------------ */

    public function getChartData(): JsonResponse
    {
        $agent = rescue(fn () => Agent::where('user_id', auth()->id())->first(), null, false);

        return response()->json(
            $agent && $agent->actif
                ? rescue(fn () => $this->graphiqueActivite($agent), $this->graphiqueVide(), false)
                : $this->graphiqueVide()
        );
    }

    private function graphiqueVide(): array
    {
        return ['labels' => [], 'messages' => [], 'documents' => [], 'donnees' => []];
    }

    /** Activité des 6 derniers mois (messages, documents, échanges de données) autour de l'agent. */
    private function graphiqueActivite(Agent $agent): array
    {
        $debut = now()->startOfMonth()->subMonths(5);
        $mois = collect(range(0, 5))->map(fn ($i) => $debut->copy()->addMonths($i));

        $cles = $mois->map(fn ($m) => $m->format('Y-m'))->all();

        $parMois = function ($requete) use ($cles, $debut): array {
            $groupes = $requete->where('created_at', '>=', $debut)->get(['created_at'])
                ->groupBy(fn ($ligne) => $ligne->created_at->format('Y-m'))
                ->map->count();

            return array_map(fn ($cle) => (int) ($groupes[$cle] ?? 0), $cles);
        };

        $conversations = $agent->conversations()->pluck('conversations.id');

        return [
            'labels'    => $mois->map(fn ($m) => ucfirst($m->translatedFormat('M Y')))->all(),
            'messages'  => $parMois(Message::whereIn('conversation_id', $conversations)),
            'documents' => $parMois(Document::whereHas('destinataires', fn ($q) => $q->where('agent_id', $agent->id))),
            'donnees'   => $parMois(EchangeDonnees::whereHas('destinataires', fn ($q) => $q->where('agent_id', $agent->id))),
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Profil                                                             */
    /* ------------------------------------------------------------------ */

    public function edit(Request $request): View
    {
        return view('profile.partials.update-profile-information-form', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Nom / prénom vivent sur la Personne rattachée, pas sur le User
        $user->personne->update([
            'nom'    => $validated['nom'],
            'prenom' => $validated['prenom'],
        ]);

        if ($user->email !== $validated['email']) {
            $user->email_verified_at = null;
        }

        $user->email = $validated['email'];
        $user->save();

        return Redirect::route('admin.profile.edit')->with('success', 'Profil mis à jour avec succès');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function editPassword(Request $request): View
    {
        return view('profile.partials.update-password-form', [
            'user' => $request->user(),
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(12)],
        ]);

        if (Hash::check($request->password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => 'Le nouveau mot de passe doit être différent de l\'actuel.',
            ]);
        }

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return Redirect::route('admin.profile.password.edit')->with('success', 'Mot de passe mis à jour avec succès');
    }
}