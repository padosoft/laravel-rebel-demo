<?php

namespace App\Models;

use BenBjurstrom\Otpz\Models\Concerns\HasOtps;
use BenBjurstrom\Otpz\Models\Concerns\Otpable;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laragear\TwoFactor\Contracts\TwoFactorAuthenticatable;
use Laragear\TwoFactor\TwoFactorAuthentication;
use Padosoft\Rebel\StepUp\Contracts\HasStepUpEmail;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Models\Concerns\InteractsWithPasskeys;
use Spatie\OneTimePasswords\Models\Concerns\HasOneTimePasswords;

#[Fillable(['name', 'email', 'password', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements
    HasStepUpEmail,
    HasPasskeys,
    MustVerifyEmail,
    TwoFactorAuthenticatable,
    Otpable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // ── Passkeys (spatie/laravel-passkeys) ───────────────────────────────────
    // InteractsWithPasskeys adds passkeys() relation + getPasskey* methods.
    use InteractsWithPasskeys;

    // ── One-time passwords (spatie/laravel-one-time-passwords) ───────────────
    // HasOneTimePasswords adds oneTimePasswords() relation + sendOneTimePassword().
    // Activates the `spatie_otp` step-up driver (bridge-spatie-otp).
    use HasOneTimePasswords;

    // ── TOTP 2FA (laragear/two-factor) ───────────────────────────────────────
    // TwoFactorAuthentication adds twoFactorAuth() polymorphic relation.
    // Activates the `laragear_totp` step-up driver (bridge-laragear-2fa).
    use TwoFactorAuthentication;

    // ── Otpz email-OTP (benbjurstrom/otpz) ──────────────────────────────────
    // HasOtps adds otps() relation. Otpable interface requires MustVerifyEmail.
    // Activates the `otpz` step-up driver (bridge-otpz).
    use HasOtps;

    /**
     * The address the email-OTP step-up driver sends its code to. Implementing
     * HasStepUpEmail is what makes the 'email_otp' step-up driver available for
     * this user (laravel-rebel-step-up).
     */
    public function stepUpEmail(): string
    {
        return $this->email;
    }

    /**
     * MustVerifyEmail — required by Otpable. For this demo we treat all seeded
     * accounts as already verified (email_verified_at is set by the seeder).
     * hasVerifiedEmail() returns true because the Authenticatable base reads the
     * 'email_verified_at' column (set in migrations).
     */

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }
}
