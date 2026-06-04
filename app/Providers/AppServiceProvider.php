<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Padosoft\Rebel\Bridge\Passkeys\Challengers\SpatiePasskeyChallenger;
use Padosoft\Rebel\Bridge\Passkeys\Contracts\PasskeyChallenger;
use Padosoft\Rebel\Core\Context\DeviceContext;
use Padosoft\Rebel\Core\Contracts\DeviceTrust;
use Padosoft\Rebel\Core\Contracts\KeyedHasher;
use Padosoft\Rebel\Sessions\Enums\SessionType;
use Padosoft\Rebel\Sessions\SessionManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the PasskeyChallenger contract so the bridge-passkeys service
        // provider can register the 'passkeys' step-up driver into DriverRegistry.
        // SpatiePasskeyChallenger is feature-detected: it only resolves if
        // spatie/laravel-passkeys is installed (it is, in this demo).
        if (class_exists(SpatiePasskeyChallenger::class)) {
            $this->app->singleton(PasskeyChallenger::class, SpatiePasskeyChallenger::class);
        }

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

        // On every login, register a real device + session in the Rebel registries
        // (laravel-rebel-sessions) so the admin panel's Device & Session Trust section
        // shows real data instead of being empty. Best-effort for the demo.
        Event::listen(Login::class, function (Login $event): void {
            if (! $event->user instanceof User) {
                return;
            }
            try {
                $request = request();
                $hasher = app(KeyedHasher::class);
                $fingerprint = $hasher->hash(($request->ip() ?? 'cli').'|'.($request->userAgent() ?? 'unknown'));
                $deviceId = substr(hash('sha256', $fingerprint->hash), 0, 16);

                app(SessionManager::class)->start($event->user, SessionType::Session, $deviceId);
                app(DeviceTrust::class)->trust($event->user, new DeviceContext($deviceId, $fingerprint->hash), 30);
            } catch (\Throwable) {
                // demo best-effort — never block login if the registry isn't available
            }
        });
    }
}
