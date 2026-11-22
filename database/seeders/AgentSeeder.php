<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Etat;
use App\Models\Organisme;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgentSeeder extends Seeder
{
    /**
     * Habilitations par rôle :
     * 1 = Public
     * 2 = Restreint
     * 3 = Confidentiel
     * 4 = Secret
     */
    private array $habilitations = [
        'super-admin' => 4,
        'admin'       => 4,
        'user'        => 2,
    ];

    /**
     * Rôles autorisés à administrer le module d'échanges.
     */
    private array $rolesAdmin = [
        'super-admin',
        'admin',
    ];

    /**
     * Organisme correspondant à chaque pays.
     */
    private array $organismesParPays = [
        'BF' => 'DGTI',
        'ML' => 'MSPC',
        'NE' => 'MISP',
    ];

    /**
     * Exceptions par e-mail :
     * email => [habilitation, est_admin, fonction]
     */
    private array $exceptions = [
        // 'rouamba@gmail.com' => [3, false, 'Chargé d\'études'],
    ];

    public function run(): void
    {
        $users = User::with('roles')
            ->orderBy('created_at')
            ->get();

        if ($users->isEmpty()) {
            $this->command?->warn(
                'Aucun compte utilisateur : lancez d’abord UserSeeder.'
            );

            return;
        }

        foreach ($users as $user) {

            // 1. Récupérer la personne associée à l'utilisateur
            $personne = null;

            if ($user->personne_id) {
                $personne = DB::table('personnes')
                    ->where('id', $user->personne_id)
                    ->first();
            }

            if (!$personne) {
                $this->command?->warn(
                    "Personne introuvable pour {$user->email}"
                );

                continue;
            }

            // 2. Déterminer le pays de résidence
            $pays = trim($personne->pays_residence ?? '');

            $paysCodes = [
                'Burkina Faso' => 'BF',
                'Mali'         => 'ML',
                'Niger'        => 'NE',
            ];

            $codeEtat = $paysCodes[$pays] ?? null;

            if (!$codeEtat) {
                $this->command?->warn(
                    "Pays non reconnu pour {$user->email} : {$pays}"
                );

                continue;
            }

            // 3. Récupérer le sigle de l'organisme du pays
            $sigle = $this->organismesParPays[$codeEtat] ?? null;

            if (!$sigle) {
                $this->command?->warn(
                    "Aucun organisme configuré pour {$pays}."
                );

                continue;
            }

            // 4. Récupérer l'État
            $etat = Etat::where('code', $codeEtat)->first();

            if (!$etat) {
                $this->command?->warn(
                    "État {$codeEtat} introuvable."
                );

                continue;
            }

            // 5. Rechercher l'organisme correspondant au pays
            $organisme = Organisme::where('etat_id', $etat->id)
                ->where('sigle', $sigle)
                ->first();

            if (!$organisme) {
                $this->command?->warn(
                    "Organisme {$sigle} introuvable pour {$pays} ({$codeEtat})."
                );

                continue;
            }

            // 6. Récupérer les rôles Spatie
            $roles = $user->roles->pluck('name')->all();

            // 7. Déterminer l'habilitation maximale
            $habilitation = collect($roles)
                ->map(fn ($role) => $this->habilitations[$role] ?? 1)
                ->max() ?? 1;

            // 8. Déterminer si l'utilisateur est administrateur
            $admin = count(
                array_intersect($roles, $this->rolesAdmin)
            ) > 0;

            // 9. Déterminer sa fonction
            $fonction = $roles
                ? ucfirst($roles[0])
                : null;

            // 10. Appliquer les exceptions personnalisées
            if (isset($this->exceptions[$user->email])) {
                [
                    $habilitation,
                    $admin,
                    $fonction
                ] = $this->exceptions[$user->email];
            }

            // 11. Créer ou mettre à jour l'agent
            $agent = Agent::updateOrCreate(
                [
                    'user_id' => $user->id,
                ],
                [
                    'organisme_id' => $organisme->id,
                    'nom'          => $this->nomDe($user, $personne),
                    'fonction'     => $fonction,
                    'habilitation' => $habilitation,
                    'est_admin'    => $admin,
                    'actif'        => (bool) ($user->est_actif ?? true),
                ]
            );

            // 12. Afficher le résultat
            $this->command?->info(sprintf(
                'Agent : %-28s %-25s | %-15s | %s | habilitation %d%s',
                $agent->nom,
                $user->email,
                $pays,
                $sigle,
                $habilitation,
                $admin ? ' (admin)' : ''
            ));
        }
    }

    /**
     * Récupérer le nom de l'agent :
     * Prénom NOM depuis la table personnes.
     */
    private function nomDe(User $user, ?object $personne = null): string
    {
        if ($personne) {
            return trim(
                $personne->prenom . ' ' . mb_strtoupper($personne->nom)
            );
        }

        return strstr((string) $user->email, '@', true) ?: 'Agent';
    }
}
