<?php

namespace App\Http\Controllers\Echange;

use App\Http\Controllers\Concerns\ResoutAgentCourant;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Etat;
use App\Models\JournalAudit;
use App\Models\Organisme;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Administration du module d'échanges : agents, organismes, clés, audit.
 * Réservé aux agents dont est_admin est vrai. Aucune clé privée ni aucun contenu n'est accessible ici.
 */
class AdministrationController extends Controller
{
    use ResoutAgentCourant;

    /** Agent connecté, à condition qu'il soit administrateur. */
    private function admin(Request $request): Agent
    {
        $agent = $this->agentCourant($request);
        abort_unless($agent->estAdmin(), 403, 'Réservé aux administrateurs.');

        return $agent;
    }

    /* ------------------------------------------------------------------ */
    /*  Agents                                                             */
    /* ------------------------------------------------------------------ */

    public function agents(Request $request): JsonResponse
    {
        $this->admin($request);

        $liste = Agent::with(['user:id,email', 'organisme.etat', 'cleActive'])
            ->orderBy('nom')
            ->get()
            ->map(fn (Agent $a) => [
                'id'           => $a->id,
                'nom'          => $a->nom,
                'email'        => $a->user?->email,
                'fonction'     => $a->fonction,
                'habilitation' => $a->habilitation,
                'est_admin'    => $a->est_admin,
                'actif'        => $a->actif,
                'organisme'    => [
                    'id'    => $a->organisme_id,
                    'sigle' => $a->organisme?->sigle,
                    'etat'  => $a->organisme?->etat?->code,
                ],
                'a_cle'        => $a->cleActive !== null,
                'cle_depuis'   => $a->cleActive?->created_at,
            ]);

        return response()->json($liste);
    }

    /** Comptes utilisateurs qui ne sont pas encore des agents. */
    public function utilisateurs(Request $request): JsonResponse
    {
        $this->admin($request);

        $users = User::query()
            ->whereNotIn('id', Agent::query()->select('user_id'))
            ->orderBy('email')
            ->get(['id', 'email', 'personne_id']);

        $personnes = DB::table('personnes')
            ->whereIn('id', $users->pluck('personne_id')->filter()->all())
            ->get()
            ->keyBy('id');

        return response()->json($users->map(fn (User $u) => [
            'id'    => $u->id,
            'email' => $u->email,
            'nom'   => $this->nomAffichage($u, $personnes->get($u->personne_id)),
        ])->values());
    }

    public function creerAgent(Request $request): JsonResponse
    {
        $admin = $this->admin($request);

        $data = $request->validate([
            'user_id'      => ['required', 'uuid', 'exists:users,id', 'unique:agents,user_id'],
            'organisme_id' => ['required', 'uuid', 'exists:organismes,id'],
            'habilitation' => ['required', 'integer', 'between:1,4'],
            'fonction'     => ['nullable', 'string', 'max:150'],
            'est_admin'    => ['boolean'],
        ]);

        $user = User::findOrFail($data['user_id']);
        $personne = $user->personne_id ? DB::table('personnes')->where('id', $user->personne_id)->first() : null;

        $agent = Agent::create([
            'user_id'      => $user->id,
            'organisme_id' => $data['organisme_id'],
            'nom'          => $this->nomAffichage($user, $personne),
            'fonction'     => $data['fonction'] ?? null,
            'habilitation' => $data['habilitation'],
            'est_admin'    => (bool) ($data['est_admin'] ?? false),
            'actif'        => true,
        ]);

        JournalAudit::noter($admin, 'admin.agent.cree', $agent);

        return response()->json(['id' => $agent->id], 201);
    }

    public function modifierAgent(Request $request, Agent $agent): JsonResponse
    {
        $admin = $this->admin($request);

        $data = $request->validate([
            'organisme_id' => ['sometimes', 'uuid', 'exists:organismes,id'],
            'habilitation' => ['sometimes', 'integer', 'between:1,4'],
            'fonction'     => ['sometimes', 'nullable', 'string', 'max:150'],
            'est_admin'    => ['sometimes', 'boolean'],
            'actif'        => ['sometimes', 'boolean'],
        ]);

        $admin_apres = (bool) ($data['est_admin'] ?? $agent->est_admin);
        $actif_apres = (bool) ($data['actif'] ?? $agent->actif);

        // On ne se coupe pas soi-même l'accès à l'administration
        abort_if(
            $agent->id === $admin->id && (! $admin_apres || ! $actif_apres),
            422,
            'Vous ne pouvez ni retirer vos propres droits d\'administration, ni désactiver votre propre compte.'
        );

        // Il doit toujours rester au moins un administrateur actif
        $autresAdmins = Agent::where('est_admin', true)->where('actif', true)->where('id', '!=', $agent->id)->count();
        abort_if(
            $autresAdmins === 0 && (! $admin_apres || ! $actif_apres),
            422,
            'Il doit rester au moins un administrateur actif.'
        );

        $agent->fill($data)->save();
        JournalAudit::noter($admin, 'admin.agent.modifie', $agent);

        return response()->json(['id' => $agent->id]);
    }

    /**
     * Révoque la clé publique active d'un agent (clé perdue ou compromise).
     * Plus personne ne pourra chiffrer vers lui tant qu'il n'aura pas créé un nouveau trousseau.
     */
    public function revoquerCle(Request $request, Agent $agent): JsonResponse
    {
        $admin = $this->admin($request);

        $nb = $agent->cles()->whereNull('revoquee_le')->update(['revoquee_le' => now()]);
        JournalAudit::noter($admin, 'admin.cle.revoquee', $agent);

        return response()->json(['revoquees' => $nb]);
    }

    /* ------------------------------------------------------------------ */
    /*  Organismes                                                         */
    /* ------------------------------------------------------------------ */

    public function organismes(Request $request): JsonResponse
    {
        $this->admin($request);

        return response()->json([
            'etats'      => Etat::orderBy('code')->get(['id', 'code', 'nom']),
            'organismes' => Organisme::with('etat:id,code')
                ->withCount('agents')
                ->orderBy('etat_id')
                ->orderBy('sigle')
                ->get()
                ->map(fn (Organisme $o) => [
                    'id'         => $o->id,
                    'etat_id'    => $o->etat_id,
                    'etat'       => $o->etat?->code,
                    'sigle'      => $o->sigle,
                    'nom'        => $o->nom,
                    'nb_agents'  => $o->agents_count,
                ]),
        ]);
    }

    public function creerOrganisme(Request $request): JsonResponse
    {
        $admin = $this->admin($request);

        $data = $request->validate([
            'etat_id' => ['required', 'uuid', 'exists:etats,id'],
            'sigle'   => [
                'required', 'string', 'max:30',
                Rule::unique('organismes')->where('etat_id', $request->input('etat_id')),
            ],
            'nom'     => ['required', 'string', 'max:255'],
        ]);

        $organisme = Organisme::create($data);
        JournalAudit::noter($admin, 'admin.organisme.cree', $organisme);

        return response()->json(['id' => $organisme->id], 201);
    }

    public function modifierOrganisme(Request $request, Organisme $organisme): JsonResponse
    {
        $admin = $this->admin($request);

        $data = $request->validate([
            'sigle' => [
                'sometimes', 'string', 'max:30',
                Rule::unique('organismes')->where('etat_id', $organisme->etat_id)->ignore($organisme->id),
            ],
            'nom'   => ['sometimes', 'string', 'max:255'],
        ]);

        $organisme->fill($data)->save();
        JournalAudit::noter($admin, 'admin.organisme.modifie', $organisme);

        return response()->json(['id' => $organisme->id]);
    }

    public function supprimerOrganisme(Request $request, Organisme $organisme): JsonResponse
    {
        $admin = $this->admin($request);

        abort_if($organisme->agents()->exists(), 422, 'Des agents sont rattachés à cet organisme : déplacez-les d\'abord.');

        JournalAudit::noter($admin, 'admin.organisme.supprime', $organisme);
        $organisme->delete();

        return response()->json(['supprime' => true]);
    }

    /* ------------------------------------------------------------------ */
    /*  Audit                                                              */
    /* ------------------------------------------------------------------ */

    /** 200 dernières entrées du journal, filtrables par action. Jamais de contenu de message. */
    public function audit(Request $request): JsonResponse
    {
        $this->admin($request);

        $filtre = trim((string) $request->query('action', ''));

        $entrees = JournalAudit::with('agent:id,nom')
            ->when($filtre !== '', fn ($q) => $q->where('action', 'like', '%' . str_replace(['%', '_'], ['\%', '\_'], $filtre) . '%'))
            ->orderByDesc('created_at')
            ->limit(200)
            ->get()
            ->map(fn (JournalAudit $j) => [
                'id'         => $j->id,
                'action'     => $j->action,
                'cible_type' => $j->cible_type,
                'cible_id'   => $j->cible_id,
                'ip'         => $j->ip,
                'created_at' => $j->created_at,
                'agent'      => $j->agent ? ['id' => $j->agent->id, 'nom' => $j->agent->nom] : null,
            ]);

        return response()->json($entrees);
    }

    /** « Prénom NOM » depuis la table personnes, sinon la partie locale de l'e-mail. */
    private function nomAffichage(User $user, ?object $personne): string
    {
        if ($personne) {
            return trim($personne->prenom . ' ' . mb_strtoupper($personne->nom));
        }

        return strstr((string) $user->email, '@', true) ?: 'Agent';
    }
}