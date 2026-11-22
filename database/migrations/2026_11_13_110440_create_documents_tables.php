<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Un document = un fichier DÉJÀ CHIFFRÉ par le navigateur. Le serveur ne connaît ni son nom, ni son contenu.
        Schema::create('documents', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('expediteur_id')->constrained('agents');
            $t->unsignedTinyInteger('niveau')->default(1);   // classification 1 à 4
            $t->string('chemin');                             // fichier chiffré (disque privé, jamais public)
            $t->unsignedBigInteger('taille');                 // taille du fichier chiffré
            $t->string('header', 64);                         // en-tête du flux chiffré (base64)
            $t->string('meta_nonce', 64);
            $t->text('meta_chiffree');                        // nom, type, taille réelle : chiffrés
            $t->string('hash', 64);                           // empreinte BLAKE2b du fichier chiffré (base64)
            $t->string('signature', 128);                     // signature de l'expéditeur (Ed25519)
            $t->timestamps();
            $t->index('created_at');
        });

        // Enveloppe : clé du document chiffrée pour chaque destinataire (l'expéditeur compris)
        Schema::create('document_destinataires', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('document_id')->constrained('documents')->cascadeOnDelete();
            $t->foreignUuid('agent_id')->constrained('agents')->cascadeOnDelete();
            $t->text('cle_chiffree');                         // base64 (crypto_box_seal)
            $t->timestamp('telecharge_le')->nullable();
            $t->timestamps();
            $t->unique(['document_id', 'agent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_destinataires');
        Schema::dropIfExists('documents');
    }
};