<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Photo facultative (personne ou véhicule recherchés) : fichier DÉJÀ CHIFFRÉ par le navigateur.
        Schema::table('echanges_donnees', function (Blueprint $t) {
            $t->string('photo_chemin')->nullable()->after('signature');   // disque privé, jamais public
            $t->string('photo_header', 64)->nullable()->after('photo_chemin');  // en-tête du flux chiffré (base64)
            $t->string('photo_hash', 64)->nullable()->after('photo_header');    // empreinte BLAKE2b du fichier chiffré
            $t->unsignedBigInteger('photo_taille')->nullable()->after('photo_hash');
        });
    }

    public function down(): void
    {
        Schema::table('echanges_donnees', function (Blueprint $t) {
            $t->dropColumn(['photo_chemin', 'photo_header', 'photo_hash', 'photo_taille']);
        });
    }
};