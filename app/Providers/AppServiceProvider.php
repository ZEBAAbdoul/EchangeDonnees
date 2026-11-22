<?php

namespace App\Providers;

use App\Models\Inscription;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();

        Password::defaults(fn() => Password::min(12));

        // ---------- Limiteur de débit du formulaire de contact ----------
        RateLimiter::for('contact', function (Request $request) {
            // Pas de limite en local, pour pouvoir tester librement
            if (app()->environment('local')) {
                return Limit::none();
            }

            return [
                Limit::perMinute(2)->by($request->ip()),
                Limit::perDay(10)->by($request->ip()),
            ];
        });

        // ---------- Cloche de notification : inscriptions « en_cours » ----------
        View::composer('components.navbar', function ($view) {
            // ... inchangé
        });

        // ---------- E-mail « Réinitialisation du mot de passe » ----------
        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            // ... inchangé
        });

        // ---------- E-mail « Vérification de l'adresse e-mail » ----------
        VerifyEmail::toMailUsing(function ($notifiable, string $verificationUrl) {
            // ... inchangé
        });

        
    } // <- fin de boot()
} // <- fin de la classe
