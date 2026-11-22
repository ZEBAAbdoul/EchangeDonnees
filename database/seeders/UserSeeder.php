<?php

namespace Database\Seeders;

use App\Models\Personne;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('UserSeeder ignoré en production.');
            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super-admin', 'admin', 'user'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // ==============================
        // BURKINA FASO
        // ==============================

        $this->createUser(
            prenom: 'Abdoul-Abass',
            nom: 'ZEBA',
            email: 'zeba@gmail.com',
            pieceNumero: 'B00000001',
            role: 'super-admin',
            pays: 'Burkina Faso',
            indicatif: '+226',
            codePays: 'BF',
        );

        $this->createUser(
            prenom: 'Boudasida',
            nom: 'ROUAMBA',
            email: 'rouamba@gmail.com',
            pieceNumero: 'B000000038',
            role: 'user',
            pays: 'Burkina Faso',
            indicatif: '+226',
            codePays: 'BF',
        );


        // ==============================
        // MALI
        // ==============================

        $this->createUser(
            prenom: 'Valérie',
            nom: 'KAMOUNI',
            email: 'kamval@dgti.ml',
            pieceNumero: 'B00000002',
            role: 'admin',
            pays: 'Mali',
            indicatif: '+223',
            codePays: 'ML',
        );

        $this->createUser(
            prenom: 'DIALLO',
            nom: 'Abdoulaye',
            email: 'user@mali.ml',
            pieceNumero: 'ML00000002',
            role: 'user',
            pays: 'Mali',
            indicatif: '+223',
            codePays: 'ML',
        );

        // ==============================
        // NIGER
        // ==============================

        $this->createUser(
            prenom: 'Sougrenoma Idrissa',
            nom: 'OUEDRAOGO',
            email: 'dri@dgti.ng',
            pieceNumero: 'B00000007',
            role: 'admin',
            pays: 'Niger',
            indicatif: '+227',
            codePays: 'NE',
        );

        $this->createUser(
            prenom: 'Hamadou',
            nom: 'HAMADOU',
            email: 'user@niger.ng',
            pieceNumero: 'NE00000002',
            role: 'user',
            pays: 'Niger',
            indicatif: '+227',
            codePays: 'NE',
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('Comptes utilisateurs BF, Mali et Niger traités.');
        $this->command?->warn(
            'Mot de passe commun : SEED_USER_PASSWORD dans .env, sinon sid@dgti2026. '
            . 'Ne jamais utiliser ces comptes de démonstration en production.'
        );
    }

    private function createUser(
        string $prenom,
        string $nom,
        string $email,
        string $pieceNumero,
        string $role,
        string $pays,
        string $indicatif,
        string $codePays,
    ): User {
        $existant = User::where('email', $email)->first();

        if ($existant) {
            if (! $existant->hasRole($role)) {
                $existant->assignRole($role);
            }

            return $existant;
        }

        $personne = Personne::firstOrCreate(
            ['piece_numero' => $pieceNumero],
            [
                'nationalite_type'    => 'nationale',
                'pays_nationalite'    => $pays,
                'nom'                 => $nom,
                'prenom'              => $prenom,
                'sexe'                => 'M',
                'date_naissance'       => '1990-01-01',
                'lieu_naissance'       => $pays,
                'piece_type'           => 'cnib',
                'telephone_indicatif'  => $indicatif,
                'telephone'            => '70000000',
                'ville'                => 'Ville principale',
                'pays_residence'       => $pays,
            ]
        );

        $user = User::create([
            'id'          => (string) Str::uuid(),
            'personne_id' => $personne->id,
            'email'       => $email,
            'password'     => bcrypt(env('SEED_USER_PASSWORD', 'sid@dgti2026')),
        ]);

        $user->assignRole($role);

        return $user;
    }
}
