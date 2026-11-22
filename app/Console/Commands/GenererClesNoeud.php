<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenererClesNoeud extends Command
{
    protected $signature = 'echange:generer-cles {--force : Remplace les clés existantes du .env}';

    protected $description = "Génère la paire de clés Ed25519 du nœud et l'écrit dans .env, sans jamais afficher la clé secrète";

    public function handle(): int
    {
        if (! function_exists('sodium_crypto_sign_keypair')) {
            $this->error("L'extension PHP sodium n'est pas activée.");

            return self::FAILURE;
        }

        $chemin = base_path('.env');
        if (! is_file($chemin) || ! is_writable($chemin)) {
            $this->error("Le fichier .env est introuvable ou non modifiable : $chemin");

            return self::FAILURE;
        }

        $env = file_get_contents($chemin);

        // Une clé « réelle » existe-t-elle déjà ? (on ignore les lignes vides ou « ... »)
        preg_match_all('/^ECHANGE_NOEUD_CLE_SECRETE=(.*)$/m', $env, $m);
        $reelles = array_filter(array_map('trim', $m[1]), fn ($v) => $v !== '' && $v !== '...');

        if ($reelles && ! $this->option('force')) {
            $this->error('Une clé existe déjà dans .env. Relancez avec --force pour la remplacer.');

            return self::FAILURE;
        }

        $paire = sodium_crypto_sign_keypair();
        $secrete = sodium_crypto_sign_secretkey($paire);
        $publique = sodium_crypto_sign_publickey($paire);

        // Contrôle : la paire doit réellement signer et vérifier.
        $signature = sodium_crypto_sign_detached('controle', $secrete);
        if (! sodium_crypto_sign_verify_detached($signature, 'controle', $publique)) {
            $this->error('La paire générée ne passe pas le contrôle de signature. Rien n\'a été écrit.');

            return self::FAILURE;
        }

        // On retire TOUTES les anciennes lignes (doublons, « ... », ancien ECHANGE_ADMIN_IDS), puis on écrit une seule paire.
        $env = preg_replace('/^(ECHANGE_NOEUD_CLE_SECRETE|ECHANGE_NOEUD_CLE_PUBLIQUE|ECHANGE_ADMIN_IDS)=.*\R?/m', '', $env);
        $env = rtrim($env) . PHP_EOL . PHP_EOL
            . 'ECHANGE_NOEUD_CLE_SECRETE=' . base64_encode($secrete) . PHP_EOL
            . 'ECHANGE_NOEUD_CLE_PUBLIQUE=' . base64_encode($publique) . PHP_EOL;

        $ecrit = file_put_contents($chemin, $env, LOCK_EX);

        $affichee = base64_encode($publique);
        sodium_memzero($secrete);
        sodium_memzero($paire);

        if ($ecrit === false) {
            $this->error("Écriture de .env impossible. Rien n'a été modifié.");

            return self::FAILURE;
        }

        $this->call('config:clear');
        $this->info('Clés générées, vérifiées et écrites dans .env (une seule paire).');
        $this->line('Clé publique (affichable sans risque) : ' . $affichee);
        $this->warn("La clé secrète n'est PAS affichée. Ne collez jamais le contenu de .env dans un message.");

        return self::SUCCESS;
    }
}