<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Un document destiné à un autre État reste invisible pour cet État tant que TOUS les administrateurs
        // actifs de l'État émetteur ne l'ont pas validé (même règle que les échanges de données).
        Schema::table('documents', function (Blueprint $t) {
            $t->foreignUuid('etat_emetteur_id')->nullable()->constrained('etats')->nullOnDelete();
            // non_requise | en_attente | validee | liberee | refusee | expiree | annulee
            $t->string('validation', 12)->default('non_requise');
            $t->timestamp('expire_le')->nullable();      // délai limite de validation
            $t->timestamp('valide_le')->nullable();      // dernière approbation obtenue
            $t->timestamp('libere_le')->nullable();      // clés remises aux destinataires des autres États
            $t->foreignUuid('libere_par')->nullable()->constrained('agents')->nullOnDelete();
            $t->index(['validation', 'expire_le']);
        });

        Schema::table('document_destinataires', function (Blueprint $t) {
            // destinataire | valideur (administrateur qui relit le document avant son envoi)
            $t->string('role', 30)->default('destinataire');
            // nouveau | attente (destinataire d'un autre État : pas encore de clé) | valideur
            $t->string('statut', 20)->default('nouveau');
        });

        // Un destinataire d'un autre État n'a PAS d'enveloppe de clé tant que le document n'est pas validé :
        // sans clé, il ne peut rien lire, même si le serveur était compromis.
        DB::statement('ALTER TABLE document_destinataires ALTER COLUMN cle_chiffree DROP NOT NULL');

        // Reprise des documents existants : on renseigne l'État émetteur
        DB::statement(
            'UPDATE documents SET etat_emetteur_id = (
                SELECT o.etat_id FROM agents a JOIN organismes o ON o.id = a.organisme_id
                WHERE a.id = documents.expediteur_id
            ) WHERE etat_emetteur_id IS NULL'
        );

        // Décision de chaque administrateur valideur (approbation ou refus), signée avec sa clé
        Schema::create('document_validations', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('document_id')->constrained('documents')->cascadeOnDelete();
            $t->foreignUuid('valideur_id')->constrained('agents')->cascadeOnDelete();
            $t->string('decision', 10)->default('en_attente');   // en_attente | approuve | refuse
            $t->string('signature', 128)->nullable();            // signature Ed25519 de la décision
            $t->text('motif_chiffre')->nullable();               // motif du refus, chiffré avec la clé du document
            $t->string('motif_nonce', 64)->nullable();
            $t->timestamp('decide_le')->nullable();
            $t->timestamps();
            $t->unique(['document_id', 'valideur_id']);
            $t->index('decision');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_validations');

        DB::statement("DELETE FROM document_destinataires WHERE role = 'valideur'");
        DB::statement("UPDATE document_destinataires SET cle_chiffree = '' WHERE cle_chiffree IS NULL");
        DB::statement('ALTER TABLE document_destinataires ALTER COLUMN cle_chiffree SET NOT NULL');

        Schema::table('document_destinataires', function (Blueprint $t) {
            $t->dropColumn(['role', 'statut']);
        });

        Schema::table('documents', function (Blueprint $t) {
            $t->dropIndex(['validation', 'expire_le']);
            $t->dropConstrainedForeignId('libere_par');
            $t->dropConstrainedForeignId('etat_emetteur_id');
            $t->dropColumn(['validation', 'expire_le', 'valide_le', 'libere_le']);
        });
    }
};