<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Padosoft\Rebel\BotProtection\Contracts\CaptchaVerifier;
use Padosoft\Rebel\BotProtection\Testing\FakeCaptchaVerifier;
use Padosoft\Rebel\Bridge\Otpz\Contracts\OtpzBroker;
use Padosoft\Rebel\Bridge\Otpz\Testing\FakeOtpzBroker;
use Padosoft\Rebel\Bridge\Passkeys\Contracts\PasskeyChallenger;
use Padosoft\Rebel\Bridge\Passkeys\Testing\FakePasskeyChallenger;
use Padosoft\Rebel\Bridge\SpatieOtp\Contracts\OneTimePasswordBroker;
use Padosoft\Rebel\Bridge\SpatieOtp\Testing\FakeOneTimePasswordBroker;
use Padosoft\Rebel\Bridge\Laragear2fa\Contracts\TwoFactorValidator;
use Padosoft\Rebel\Bridge\Laragear2fa\Testing\FakeTwoFactorValidator;
use Padosoft\Rebel\Core\Contracts\BotProtection;
use Padosoft\Rebel\StepUp\DriverRegistry;
use Tests\TestCase;

/**
 * Offline integration tests for the 9 new packages wired into feat/integrate-9-extras.
 *
 * These tests run fully offline (in-memory SQLite, no network). They assert:
 *  1. All 4 bridge step-up drivers are registered in DriverRegistry with the right keys
 *     and assurance levels.
 *  2. Bot-protection passes with a fake Turnstile token (FakeCaptchaVerifier).
 *  3. /demo/extras returns 200 for an authenticated user and contains all bridge driver keys.
 *  4. The otpz bridge driver start→verify cycle works end-to-end (offline, FakeOtpzBroker).
 */
class ExtrasIntegrationTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /** Seed the minimum data the demo routes need (admin + customer users). */
    protected function setUp(): void
    {
        parent::setUp();

        // Bind all four bridge fakes so the drivers register even without real
        // third-party state in the in-memory test DB.
        $this->app->singleton(PasskeyChallenger::class, fn () => new FakePasskeyChallenger());
        $this->app->singleton(OtpzBroker::class, fn () => new FakeOtpzBroker());
        $this->app->singleton(OneTimePasswordBroker::class, fn () => new FakeOneTimePasswordBroker());
        $this->app->singleton(TwoFactorValidator::class, fn () => new FakeTwoFactorValidator());
        $this->app->singleton(CaptchaVerifier::class, fn () => FakeCaptchaVerifier::passing());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. DriverRegistry — all 4 bridge drivers present with correct keys
    // ─────────────────────────────────────────────────────────────────────────

    public function test_passkeys_driver_is_registered(): void
    {
        $registry = $this->app->make(DriverRegistry::class);

        $this->assertNotNull($registry->get('passkeys'), 'passkeys driver should be registered');
        $this->assertSame('passkeys', $registry->get('passkeys')->key());
    }

    public function test_passkeys_driver_has_aal2_phishing_resistant(): void
    {
        $registry  = $this->app->make(DriverRegistry::class);
        $assurance = $registry->get('passkeys')->assurance();

        $this->assertTrue($assurance->phishingResistant, 'passkeys should be phishing-resistant');
        $this->assertSame('aal2', $assurance->aal->value);
    }

    public function test_spatie_otp_driver_is_registered(): void
    {
        $registry = $this->app->make(DriverRegistry::class);

        $this->assertNotNull($registry->get('spatie_otp'), 'spatie_otp driver should be registered');
        $this->assertSame('spatie_otp', $registry->get('spatie_otp')->key());
    }

    public function test_spatie_otp_driver_has_aal2_not_phishing_resistant(): void
    {
        $registry  = $this->app->make(DriverRegistry::class);
        $assurance = $registry->get('spatie_otp')->assurance();

        $this->assertFalse($assurance->phishingResistant, 'spatie_otp should not be phishing-resistant');
        $this->assertSame('aal2', $assurance->aal->value);
        $this->assertContains('otp', $assurance->amr);
    }

    public function test_laragear_totp_driver_is_registered(): void
    {
        $registry = $this->app->make(DriverRegistry::class);

        $this->assertNotNull($registry->get('laragear_totp'), 'laragear_totp driver should be registered');
        $this->assertSame('laragear_totp', $registry->get('laragear_totp')->key());
    }

    public function test_laragear_totp_driver_has_aal2_amr_totp(): void
    {
        $registry  = $this->app->make(DriverRegistry::class);
        $assurance = $registry->get('laragear_totp')->assurance();

        $this->assertFalse($assurance->phishingResistant);
        $this->assertSame('aal2', $assurance->aal->value);
        $this->assertContains('totp', $assurance->amr);
    }

    public function test_otpz_driver_is_registered(): void
    {
        $registry = $this->app->make(DriverRegistry::class);

        $this->assertNotNull($registry->get('otpz'), 'otpz driver should be registered');
        $this->assertSame('otpz', $registry->get('otpz')->key());
    }

    public function test_otpz_driver_has_aal2_not_phishing_resistant(): void
    {
        $registry  = $this->app->make(DriverRegistry::class);
        $assurance = $registry->get('otpz')->assurance();

        $this->assertFalse($assurance->phishingResistant);
        $this->assertSame('aal2', $assurance->aal->value);
        $this->assertContains('otp', $assurance->amr);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. Bot-protection — passes with Fake (offline)
    // ─────────────────────────────────────────────────────────────────────────

    public function test_bot_protection_passes_with_fake_verifier(): void
    {
        // FakeCaptchaVerifier::passing() is bound in setUp(), so BotProtection
        // resolves to a TurnstileBotProtection that delegates to the fake verifier.
        // Any non-empty token should pass.
        $botProtection = $this->app->make(BotProtection::class);
        $hasher  = $this->app->make(\Padosoft\Rebel\Core\Contracts\KeyedHasher::class);
        $request = \Illuminate\Http\Request::create('/demo/extras', 'GET');
        $context = \Padosoft\Rebel\Core\Context\SecurityContext::fromRequest($request, $hasher)
            ->withPurpose('test-bot-check');

        $this->assertTrue($botProtection->passes($context, 'TURNSTILE_TEST_TOKEN'));
    }

    public function test_bot_protection_fails_without_token(): void
    {
        $botProtection = $this->app->make(BotProtection::class);
        $hasher  = $this->app->make(\Padosoft\Rebel\Core\Contracts\KeyedHasher::class);
        $request = \Illuminate\Http\Request::create('/demo/extras', 'GET');
        $context = \Padosoft\Rebel\Core\Context\SecurityContext::fromRequest($request, $hasher)
            ->withPurpose('test-bot-check');

        $this->assertFalse($botProtection->passes($context, null));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. /demo/extras — HTTP 200 + all bridge driver keys rendered
    // ─────────────────────────────────────────────────────────────────────────

    public function test_extras_page_returns_200_for_authed_user(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->actingAs($user)->get('/demo/extras');
        $response->assertStatus(200);
    }

    public function test_extras_page_shows_all_bridge_driver_keys(): void
    {
        $user     = User::factory()->create(['is_admin' => false]);
        $response = $this->actingAs($user)->get('/demo/extras');

        $response->assertStatus(200);
        $response->assertSee('passkeys');
        $response->assertSee('spatie_otp');
        $response->assertSee('laragear_totp');
        $response->assertSee('otpz');
    }

    public function test_extras_page_shows_email_otp_driver(): void
    {
        $user     = User::factory()->create(['is_admin' => false]);
        $response = $this->actingAs($user)->get('/demo/extras');

        // The email_otp driver (from rebel-step-up) should also be present.
        $response->assertSee('email_otp');
    }

    public function test_extras_page_shows_turnstile_sitekey(): void
    {
        $user     = User::factory()->create(['is_admin' => false]);
        $response = $this->actingAs($user)->get('/demo/extras');

        // The test sitekey should be rendered in the page.
        $response->assertSee('1x00000000000000000000AA');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. Otpz bridge driver — start/verify cycle (offline, FakeOtpzBroker)
    //    The driver is driven directly (not via DriverRegistry) to avoid the
    //    already-constructed singleton holding a reference to the real broker.
    // ─────────────────────────────────────────────────────────────────────────

    /** Build an OtpzStepUpDriver wired to a FakeOtpzBroker — no network, no DB. */
    private function makeOtpzDriver(): array
    {
        $fakeBroker = new FakeOtpzBroker();
        $audit      = $this->app->make(\Padosoft\Rebel\Core\Contracts\AuditLogger::class);
        $driver     = new \Padosoft\Rebel\Bridge\Otpz\Drivers\OtpzStepUpDriver($fakeBroker, $audit);

        return [$driver, $fakeBroker];
    }

    /** Build a StepUpContext for a given user + purpose. */
    private function makeContext(\App\Models\User $user, string $purpose): \Padosoft\Rebel\StepUp\StepUpContext
    {
        $hasher  = $this->app->make(\Padosoft\Rebel\Core\Contracts\KeyedHasher::class);
        $request = \Illuminate\Http\Request::create('/demo/extras', 'GET');

        return new \Padosoft\Rebel\StepUp\StepUpContext(
            $user,
            $purpose,
            \Padosoft\Rebel\Core\Context\SecurityContext::fromRequest($request, $hasher)->withPurpose($purpose),
        );
    }

    public function test_otpz_driver_start_returns_reference(): void
    {
        [$driver, $fakeBroker] = $this->makeOtpzDriver();

        $user    = User::factory()->create();
        $context = $this->makeContext($user, 'test-otpz');

        $reference = $driver->start($context);

        $this->assertNotNull($reference, 'start() should return a non-null reference');
        $this->assertSame(1, $fakeBroker->pendingCount());
    }

    public function test_otpz_driver_verify_correct_code(): void
    {
        [$driver, $fakeBroker] = $this->makeOtpzDriver();

        $user    = User::factory()->create();
        $context = $this->makeContext($user, 'test-otpz');

        $reference = $driver->start($context);
        $this->assertNotNull($reference);

        $ok = $driver->verify($context, 'test-otp-code', $reference);
        $this->assertTrue($ok, 'verify() should return true for the correct code');
    }

    public function test_otpz_driver_verify_wrong_code_fails(): void
    {
        [$driver, $fakeBroker] = $this->makeOtpzDriver();

        $user    = User::factory()->create();
        $context = $this->makeContext($user, 'test-otpz');

        $reference = $driver->start($context);
        $this->assertNotNull($reference);

        $ok = $driver->verify($context, 'wrong-code', $reference);
        $this->assertFalse($ok, 'verify() should return false for a wrong code');
    }
}
