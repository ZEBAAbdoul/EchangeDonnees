<?php

use App\Http\Controllers\Echange\AdministrationController;
use App\Http\Controllers\Echange\AgentController;
use App\Http\Controllers\Echange\ConversationController;
use App\Http\Controllers\Echange\DocumentController;
use App\Http\Controllers\Echange\EchangeController;
use App\Http\Controllers\Echange\EchangeDonneesController;
use App\Http\Controllers\Echange\MessageController;
use App\Http\Controllers\Echange\ValidationEchangeController;
use Illuminate\Support\Facades\Route;

/*
 | Module « Échanges sécurisés ».
 | À inclure depuis routes/web.php :   require __DIR__ . '/echange.php';
 | Ces routes sont dans le groupe "web" (session + CSRF), car les appels viennent de la page.
 | Ajoutez ici les middlewares de votre espace admin si besoin (ex. 'verified').
 */

Route::middleware('auth')->group(function () {

    // Page
    Route::get('/admin/echange', EchangeController::class)->name('admin.echange');

    // API utilisée par la page
    Route::prefix('echange/api')->name('echange.api.')->middleware('throttle:60,1')->group(function () {

        // Agent connecté, annuaire et clés publiques
        Route::get('moi', [AgentController::class, 'moi'])->name('moi');
        Route::get('agents', [AgentController::class, 'annuaire'])->name('agents');
        Route::post('cles', [AgentController::class, 'enregistrerCle'])->name('cles.store');

        // Messages
        Route::get('conversations', [ConversationController::class, 'index'])->name('conversations.index');
        Route::post('conversations', [ConversationController::class, 'store'])->name('conversations.store');
        Route::get('conversations/{conversation}/messages', [MessageController::class, 'index'])->name('messages.index');
        Route::post('conversations/{conversation}/messages', [MessageController::class, 'store'])->name('messages.store');

        // Documents chiffrés
        Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('documents/{document}/fichier', [DocumentController::class, 'download'])->name('documents.download');

        // Données structurées
        Route::get('donnees', [EchangeDonneesController::class, 'index'])->name('donnees.index');
        Route::post('donnees', [EchangeDonneesController::class, 'store'])->name('donnees.store');
        Route::get('donnees/{echange}/photo', [EchangeDonneesController::class, 'photo'])->name('donnees.photo');
        Route::post('donnees/{echange}/statut', [EchangeDonneesController::class, 'statut'])->name('donnees.statut');

        // Validation des envois vers un autre État (décision signée, transmission des clés, annulation)
        Route::post('donnees/{id}/validation', [ValidationEchangeController::class, 'decider'])->name('donnees.validation');
        Route::post('donnees/{id}/liberer', [ValidationEchangeController::class, 'liberer'])->name('donnees.liberer');
        Route::post('donnees/{id}/annuler', [ValidationEchangeController::class, 'annuler'])->name('donnees.annuler');

        // Administration (réservée aux agents administrateurs : contrôle dans le contrôleur)
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('agents', [AdministrationController::class, 'agents'])->name('agents');
            Route::get('utilisateurs', [AdministrationController::class, 'utilisateurs'])->name('utilisateurs');
            Route::post('agents', [AdministrationController::class, 'creerAgent'])->name('agents.store');
            Route::patch('agents/{agent}', [AdministrationController::class, 'modifierAgent'])->name('agents.update');
            Route::post('agents/{agent}/revoquer-cle', [AdministrationController::class, 'revoquerCle'])->name('agents.revoquer');

            Route::get('organismes', [AdministrationController::class, 'organismes'])->name('organismes');
            Route::post('organismes', [AdministrationController::class, 'creerOrganisme'])->name('organismes.store');
            Route::patch('organismes/{organisme}', [AdministrationController::class, 'modifierOrganisme'])->name('organismes.update');
            Route::delete('organismes/{organisme}', [AdministrationController::class, 'supprimerOrganisme'])->name('organismes.destroy');

            Route::get('audit', [AdministrationController::class, 'audit'])->name('audit');
        });
    });
});
