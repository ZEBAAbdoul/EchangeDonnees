<?php

use App\Http\Controllers\ParametresSiteController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {

    // ==================== DASHBOARD ====================

    Route::get('/dashboard', [ProfileController::class, 'dashboard'])
        ->name('dashboard');

    Route::get('/dashboard/chart-data', [ProfileController::class, 'getChartData'])
        ->name('dashboard.chart-data');


    // ==================== PROFIL ====================

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('profile.password.edit');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');


    // ==================== UTILISATEURS (super-admin et admin) ====================

    Route::middleware('role:super-admin|admin')->group(function () {

        // « stats » AVANT la ressource, sinon elle est interprétée comme users/{user}
        Route::get('/users/stats', [UserController::class, 'stats'])->name('user.stats');

        Route::resource('users', UserController::class)->names('user');

        Route::patch('users/{user}/toggle-actif', [UserController::class, 'toggle'])
            ->middleware('role:super-admin')
            ->name('user.toggle-actif');

        Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->middleware('role:super-admin')
            ->name('user.reset-password');
    });


    // ==================== RÔLES ET PERMISSIONS ====================

    Route::middleware(['role:Gérant'])->group(function () {
        Route::resource('role', RoleController::class);
        Route::resource('permission', PermissionController::class);
    });


    // ==================== PARAMÈTRES DU SITE ====================

    Route::get('parametres/public', [ParametresSiteController::class, 'getPublicSettings'])
        ->name('parametres.public');

    Route::prefix('parametres')->name('parametres.')->group(function () {
        Route::get('/', [ParametresSiteController::class, 'index'])->name('index');
        Route::get('/{parametre}', [ParametresSiteController::class, 'show'])->name('show');
        Route::post('/', [ParametresSiteController::class, 'store'])->name('store');
        Route::put('/{parametre}', [ParametresSiteController::class, 'update'])->name('update');
        Route::delete('/{parametre}', [ParametresSiteController::class, 'destroy'])->name('destroy');
    });

});