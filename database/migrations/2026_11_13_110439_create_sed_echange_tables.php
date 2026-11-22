<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pays de l'AES
        Schema::create('etats', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('code', 3)->unique();      // BF, ML, NE
            $t->string('nom');
            $t->timestamps();
        });

        // Ministères, directions, services...
        Schema::create('organismes', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('etat_id')->constrained('etats')->cascadeOnDelete();
            $t->string('sigle', 30);
            $t->string('nom');
            $t->timestamps();
            $t->unique(['etat_id', 'sigle']);
        });

        // Agent = compte utilisateur + identité cryptographique
        Schema::create('agents', function (Blueprint $t) {
            $t->uuid('id')->primary();
            // Adapte au type de users.id (ici UUID)
            $t->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $t->foreignUuid('organisme_id')->constrained('organismes');
            $t->string('nom');
            $t->string('fonction')->nullable();
            // 1 = Public, 2 = Restreint, 3 = Confidentiel, 4 = Secret
            $t->unsignedTinyInteger('habilitation')->default(1);
            $t->boolean('est_admin')->default(false);
            $t->boolean('actif')->default(true);
            $t->timestamps();
        });

        // Clés publiques (historique + révocation). La clé privée ne quitte jamais le navigateur.
        Schema::create('cles_publiques', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('agent_id')->constrained('agents')->cascadeOnDelete();
            $t->string('cle_chiffrement', 64);    // X25519 en base64
            $t->string('cle_signature', 64);      // Ed25519 en base64
            $t->timestamp('revoquee_le')->nullable();
            $t->timestamps();
            $t->index(['agent_id', 'revoquee_le']);
        });

        Schema::create('conversations', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('titre')->nullable();      // null = discussion 1 à 1
            $t->boolean('est_groupe')->default(false);
            $t->foreignUuid('cree_par')->constrained('agents');
            $t->timestamps();
        });

        Schema::create('conversation_membres', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('conversation_id')->constrained()->cascadeOnDelete();
            $t->foreignUuid('agent_id')->constrained('agents')->cascadeOnDelete();
            $t->timestamp('dernier_lu_le')->nullable();
            $t->timestamps();
            $t->unique(['conversation_id', 'agent_id']);
        });

        Schema::create('messages', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('conversation_id')->constrained()->cascadeOnDelete();
            $t->foreignUuid('expediteur_id')->constrained('agents');
            $t->unsignedTinyInteger('niveau')->default(1);   // classification
            $t->longText('contenu_chiffre');                  // base64
            $t->string('nonce', 64);
            $t->string('signature', 128);
            $t->timestamps();
            $t->index(['conversation_id', 'created_at']);
        });

        // Enveloppe : clé du message chiffrée pour chaque destinataire
        Schema::create('message_cles', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('message_id')->constrained()->cascadeOnDelete();
            $t->foreignUuid('agent_id')->constrained('agents')->cascadeOnDelete();
            $t->text('cle_chiffree');                         // base64 (crypto_box_seal)
            $t->timestamps();
            $t->unique(['message_id', 'agent_id']);
        });

        // Journal d'audit : qui a fait quoi, sans le contenu
        Schema::create('journal_audit', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $t->string('action', 60);                         // message.envoye, cle.enregistree...
            $t->string('cible_type', 40)->nullable();
            $t->uuid('cible_id')->nullable();
            $t->string('ip', 45)->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->index(['agent_id', 'created_at']);
        });
    }

    public function down(): void
    {
        foreach (['journal_audit', 'message_cles', 'messages', 'conversation_membres',
                  'conversations', 'cles_publiques', 'agents', 'organismes', 'etats'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
