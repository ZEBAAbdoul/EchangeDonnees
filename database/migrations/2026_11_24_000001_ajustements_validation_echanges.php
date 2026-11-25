<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Si « statut » a été créé avec ->enum(), PostgreSQL a posé une contrainte CHECK qui refuserait 'attente' et 'valideur'
        DB::statement('ALTER TABLE echanges_donnees_destinataires DROP CONSTRAINT IF EXISTS echanges_donnees_destinataires_statut_check');

        // Pour l'expiration automatique
        Schema::table('echanges_donnees', function (Blueprint $t) {
            $t->index(['validation', 'expire_le']);
        });
    }

    public function down(): void
    {
        Schema::table('echanges_donnees', function (Blueprint $t) {
            $t->dropIndex(['validation', 'expire_le']);
        });
    }
};