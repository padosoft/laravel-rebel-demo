<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Laravel Rebel's admin panel + admin API are fail-closed: they require the
        // authenticated user to pass the 'rebel-admin' Gate ability. In this demo we
        // grant it to users whose `is_admin` flag is set (see the demo seeder).
        Gate::define('rebel-admin', static function ($user): bool {
            return (bool) ($user->is_admin ?? false);
        });

        // Fortify ships the auth pipeline but not the UI. laravel-rebel-bridge-fortify
        // turns Fortify's password/TOTP/passkey confirmations into Rebel step-up drivers;
        // here we just give Fortify a minimal login view so /login works in the demo.
        if (class_exists(Fortify::class)) {
            Fortify::loginView(fn () => view('auth.login'));
            Fortify::twoFactorChallengeView(fn () => view('auth.two-factor'));
            Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
            Fortify::resetPasswordView(fn ($request) => view('auth.reset-password', ['request' => $request]));
        }
    }
}
