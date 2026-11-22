<?php

use Illuminate\Http\Request;


use App\Http\Controllers\AgentController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\CleAgentController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EchangeDonneesController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

 
// Toutes les routes exigent un agent authentifié (guard "agent").
Route::middleware(['auth:agent', 'throttle:60,1'])->group(function () {
 
    // Annuaire et clés publiques
    Route::get('agents', [AgentController::class, 'index']);
    Route::post('agents', [AgentController::class, 'store']);                    // administrateur
    Route::post('agents/{agent}/revoquer', [AgentController::class, 'revoquer']); // administrateur
    Route::get('agents/{agent}/cles', [CleAgentController::class, 'index']);
    Route::post('agents/{agent}/cles', [CleAgentController::class, 'store']);     // administrateur
    Route::post('cles/{cle}/revoquer', [CleAgentController::class, 'revoquer']);
 
    // Messages
    Route::get('conversations', [ConversationController::class, 'index']);
    Route::post('conversations', [ConversationController::class, 'store']);
    Route::get('conversations/{conversation}', [ConversationController::class, 'show']);
    Route::get('conversations/{conversation}/messages', [MessageController::class, 'index']);
    Route::post('conversations/{conversation}/messages', [MessageController::class, 'store']);
    Route::post('messages/{message}/lu', [MessageController::class, 'marquerLu']);
 
    // Documents
    Route::get('documents', [DocumentController::class, 'index']);
    Route::post('documents', [DocumentController::class, 'store']);
    Route::get('documents/{document}', [DocumentController::class, 'show']);
    Route::get('documents/{document}/fichier', [DocumentController::class, 'download']);
 
    // Données structurées
    Route::get('echanges', [EchangeDonneesController::class, 'index']);
    Route::post('echanges', [EchangeDonneesController::class, 'store']);
    Route::get('echanges/{echange}', [EchangeDonneesController::class, 'show']);
    Route::post('echanges/{echange}/traiter', [EchangeDonneesController::class, 'traiter']);
 
    // Audit (administrateur)
    Route::get('audit', [AuditController::class, 'index']);
    Route::get('audit/verification', [AuditController::class, 'verifier']);
});
