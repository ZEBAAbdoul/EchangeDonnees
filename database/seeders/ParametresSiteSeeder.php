<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ParametresSiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer un utilisateur existant
        $user = DB::table('users')->first();

        // Récupérer le premier enregistrement des paramètres du site
        $parametres = DB::table('parametres_site')->first();

        if (!$parametres) {
            // Aucun paramètre n'existe encore : on crée l'enregistrement
            DB::table('parametres_site')->insert([
                'id' => (string) Str::uuid(),
                'nom_site' => "DGTI - Direction Générale des Transmissions et de l'Informatique",
                'slogan' => '-',
                'logo_url' => '/storage/logos/aes-logo.png',
                'favicon_url' => '/storage/favicons/dgti-favicon.ico',
                'mot_dg_titre' => 'Commissaire Pricipal de Police',

                'mot_dg_contenu' => <<<'TEXT'
Chers Internautes, chers partenaires,

Je suis heureux de vous accueillir sur le site de la Direction Générale des Transmissions et de l’Informatique.

Dans un monde où l’information circule toujours plus vite, la fiabilité des communications et la solidité des systèmes d’information sont devenues des conditions essentielles de l’action des forces de sécurité. C’est à cette exigence que notre direction s’attache à répondre, chaque jour, avec rigueur et sens du service public.

Ce site a été pensé pour vous permettre de mieux connaître nos missions, de suivre nos actualités et d’accéder simplement à nos plateformes en ligne. Il illustre notre volonté de rapprocher l’administration des citoyens grâce au numérique.

Je remercie l’ensemble des agents de la direction pour leur engagement, ainsi que nos partenaires pour leur confiance. Ensemble, poursuivons la modernisation de nos outils au service de la sécurité de tous.

Le Directeur Général
TEXT,

                'mot_dg_photo_url' => '/storage/dg/dg.jpg',
                'mot_dg_nom' => 'Zakaria HEBIE',
                'adresse' => '01 BP 6466 OUAGADOUGOU 01 , Sise Avenue Sanematenga, à la station terrienne d’Onatel à Somgandé',
                'telephone' => '(+226) 25 41 90 83 | (+226)  62 18 05 95 ',
                'email_contact' => 'dgti@securite.gov.bf ',
                'contact_rh' => '(+226)  62 18 05 95 ',
                'annee_creation' => 0000,
                'personne_forme' => 0000,
                'facebook_url' => 'https://web.facebook.com/dgti2021',
                'linkedin_url' => 'https://linkedin.com/school/dgti-sn',
                'meta_description' => '-',
                'updated_by' => $user ? $user->id : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            // L'enregistrement existe : on le met à jour
            DB::table('parametres_site')
                ->where('id', $parametres->id)
                ->update([
                    'nom_site' => "DGTI - Direction Générale des Technologies de l'Information",
                    'slogan' => 'Innover pour l’avenir, agir pour le développement.',
                    'logo_url' => '/storage/logos/dgti-logo.png',
                    'favicon_url' => '/storage/favicons/dgti-favicon.ico',
                    'mot_dg_titre' => 'Commissaire Pricipal de Police',

                    'mot_dg_contenu' => <<<'TEXT'
                                Chers visiteurs, chers partenaires,

                        Je suis heureux de vous accueillir sur le site de la Direction Générale des Transmissions et de l’Informatique.

                        Dans un monde où l’information circule toujours plus vite, la fiabilité des communications et la solidité des systèmes d’information sont devenues des conditions essentielles de l’action des forces de sécurité. C’est à cette exigence que notre direction s’attache à répondre, chaque jour, avec rigueur et sens du service public.

                        Ce site a été pensé pour vous permettre de mieux connaître nos missions, de suivre nos actualités et d’accéder simplement à nos plateformes en ligne. Il illustre notre volonté de rapprocher l’administration des citoyens grâce au numérique.

                        Je remercie l’ensemble des agents de la direction pour leur engagement, ainsi que nos partenaires pour leur confiance. Ensemble, poursuivons la modernisation de nos outils au service de la sécurité de tous.

                        Le Directeur Général
                        TEXT,

                    'mot_dg_photo_url' => '/storage/dg/dg.jpeg',
                    'mot_dg_nom' => 'Zakaria HEBIE',
                    'adresse' => '01 BP 6466 OUAGADOUGOU 01, Sise Avenue Sanematenga, à la station terrienne d’Onatel à Somgandé.',
                    'telephone' => '(+226) 25 41 90 83',
                    'email_contact' => 'dgti@securite.gov.bf ',
                    'contact_rh' => '(+226)  62 18 05 95 ',
                    'annee_creation' => 0000,
                    'personne_forme' => 0000,
                    'facebook_url' => 'https://web.facebook.com/dgti2021',
                    'linkedin_url' => 'https://linkedin.com/school/dgti-sn',
                    'meta_description' => '-',
                    'updated_by' => $user ? $user->id : null,
                    'updated_at' => now(),
                ]);
        }
    }
}
