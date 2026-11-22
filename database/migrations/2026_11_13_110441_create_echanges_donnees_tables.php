<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Échange de données structurées : le CONTENU (objet, champs du formulaire) est chiffré par le navigateur.
        // Le serveur ne connaît que le type, la classification, la priorité et les statuts.
        Schema::create('echanges_donnees', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('expediteur_id')->constrained('agents');
            $t->string('type', 30);                          // alerte, personne, vehicule, incident, demande
            $t->unsignedTinyInteger('niveau')->default(1);   // classification 1 à 4
            $t->string('priorite', 10)->default('normale');  // normale, haute, urgente
            $t->longText('contenu_chiffre');                 // base64 (secretbox)
            $t->string('nonce', 64);
            $t->string('signature', 128);                    // signature de l'expéditeur (Ed25519)
            $t->timestamps();
            $t->index('type');
            $t->index('created_at');
        });

        // Une ligne par destinataire (l'expéditeur a aussi la sienne, statut « emis », pour relire son envoi)
        Schema::create('echanges_donnees_destinataires', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('echange_id')->constrained('echanges_donnees')->cascadeOnDelete();
            $t->foreignUuid('agent_id')->constrained('agents')->cascadeOnDelete();
            $t->text('cle_chiffree');                        // base64 (crypto_box_seal)
            $t->string('statut', 10)->default('nouveau');    // nouveau, lu, traite, rejete, emis
            $t->timestamp('lu_le')->nullable();
            $t->timestamp('traite_le')->nullable();
            $t->timestamps();
            $t->unique(['echange_id', 'agent_id']);
            $t->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('echanges_donnees_destinataires');
        Schema::dropIfExists('echanges_donnees');
    }
};