<?php

namespace Database\Seeders;

use App\Models\Etat;
use App\Models\Organisme;
use Illuminate\Database\Seeder;

class OrganismeSeeder extends Seeder
{
    /**
     * Organismes de départ : à adapter. D'autres s'ajoutent ensuite
     * depuis l'administration (ou en complétant ce tableau).
     */
    private array $organismes = [
        'BF' => [
            ['DGTI',  "Direction Générale des Transmissions et de l'Informatique"],
            ['DGPN',  'Direction Générale de la Police Nationale'],
            ['MSECU', 'Ministère de la Sécurité'],
        ],
        'ML' => [
            ['MSPC', 'Ministère de la Sécurité et de la Protection Civile'],
        ],
        'NE' => [
            ['MISP', "Ministère de l'Intérieur et de la Sécurité Publique"],
        ],
    ];

    public function run(): void
    {
        foreach ($this->organismes as $code => $liste) {
            $etat = Etat::where('code', $code)->firstOrFail();

            foreach ($liste as [$sigle, $nom]) {
                Organisme::firstOrCreate(
                    ['etat_id' => $etat->id, 'sigle' => $sigle],
                    ['nom' => $nom]
                );
            }
        }
    }
}