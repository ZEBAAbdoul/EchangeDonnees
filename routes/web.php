<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginWithOTPController;
use Illuminate\Support\Facades\Route;

/*
|---------------------------------------------------------------------------
| Web Routes
|---------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/mentions_legale', [HomeController::class, 'mentionLegale'])->name('mentionLegale');
Route::get('/organigramme', [HomeController::class, 'organigramme'])->name('organigramme');
Route::get('/mission', [HomeController::class, 'mission'])->name('mission');
Route::get('/documents', [HomeController::class, 'document'])->name('documents');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
Route::get('/conditionutilisation', [HomeController::class, 'conditionutilisation'])->name('conditionutilisation');
Route::get('/politiqueConfidentialite', [HomeController::class, 'politiqueConfidentialite'])->name('politiqueConfidentialite');

Route::get('/', function () {
    return view('auth.login');
});

// Login with OTP Routes
Route::prefix('/otp')->middleware('guest')->name('otp.')->controller(LoginWithOTPController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::post('/generate', 'generate')->name('generate');
    Route::get('/verification/{userId}', 'verification')->name('verification');
    Route::post('login/verification', 'loginWithOtp')->name('loginWithOtp');
});

// Échanges sécurisés (page + API)
require __DIR__ . '/echange.php';

// Auth routes
require __DIR__ . '/auth.php';
// Admin Routes
require __DIR__ . '/admin.php';