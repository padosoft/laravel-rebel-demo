<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Padosoft\Rebel\AiGuard\Detection\AnomalyDetector;
use Padosoft\Rebel\AiGuard\Models\AnomalyCase;
use Padosoft\Rebel\Channels\Enums\Channel;
use Padosoft\Rebel\Channels\Routing\VerificationRouter;
use Padosoft\Rebel\Core\Audit\AuditEvent;
use Padosoft\Rebel\Core\Context\SecurityContext;
use Padosoft\Rebel\Core\Contracts\AuditLogger;
use Padosoft\Rebel\Core\Contracts\KeyedHasher;
use Padosoft\Rebel\Core\Identifiers\EmailIdentifier;
use Padosoft\Rebel\Core\Identifiers\PhoneIdentifier;
use Padosoft\Rebel\EmailOtp\RebelEmailOtp;
use Padosoft\Rebel\Recovery\RecoveryCodeManager;
use Padosoft\Rebel\Sessions\Enums\SessionType;
use Padosoft\Rebel\Sessions\SessionManager;
use Padosoft\Rebel\StepUp\RebelStepUp;
use Padosoft\Rebel\StepUp\StepUpContext;

/*
|--------------------------------------------------------------------------
| Laravel Rebel — integration demo routes
|--------------------------------------------------------------------------
| This app wires the WHOLE padosoft/laravel-rebel-* suite together and gives
| each capability a clickable demo so the ecosystem can be exercised end to
| end (front-end + back-end) in a real browser.
*/

Route::view('/', 'demo.home')->name('demo.home');

// Fortify redirects here after a successful password login.
Route::get('/home', fn () => redirect('/'))->name('home');

/**
 * Demo helper: authenticate as the seeded admin so the fail-closed admin panel
 * and admin API can be viewed.
 */
Route::get('/demo/login-as-admin', function () {
    Auth::login(User::where('email', 'admin@demo.test')->firstOrFail());

    return redirect('/admin/rebel');
})->name('demo.login-as-admin');

Route::get('/demo/login-as-customer', function () {
    Auth::login(User::where('email', 'demo.customer@example.com')->firstOrFail());

    return redirect('/')->with('status', 'Signed in as the demo customer.');
})->name('demo.login-as-customer');

Route::get('/demo/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/')->with('status', 'Signed out.');
})->name('demo.logout');

/*
|--------------------------------------------------------------------------
| Passwordless email-OTP LOGIN (real session, via laravel-rebel-email-otp)
|--------------------------------------------------------------------------
| Unlike the package's /account/login reference flow (which only demos the OTP
| mechanism), this signs the matching user in so you can then use the protected
| areas (step-up, admin panel) — the passwordless equivalent of the password login.
*/
Route::get('/demo/passwordless', fn () => view('demo.passwordless-start'))->name('demo.passwordless');

Route::post('/demo/passwordless/start', function (Request $request, RebelEmailOtp $otp, KeyedHasher $hasher) {
    $request->validate(['email' => ['required', 'email']]);
    $email = $request->string('email')->toString();
    $purpose = 'demo-passwordless-login';
    $context = SecurityContext::fromRequest($request, $hasher)->withPurpose($purpose);

    $result = $otp->start(EmailIdentifier::from($email), $purpose, $context);

    $request->session()->put('demo_pwl', ['email' => $email, 'challenge_id' => $result->challengeId, 'masked' => $result->maskedIdentifier]);

    return redirect()->route('demo.passwordless.verify');
})->name('demo.passwordless.start');

Route::get('/demo/passwordless/verify', function (Request $request) {
    $s = $request->session()->get('demo_pwl');

    return is_array($s) ? view('demo.passwordless-verify', ['masked' => $s['masked'] ?? '']) : redirect()->route('demo.passwordless');
})->name('demo.passwordless.verify');

Route::post('/demo/passwordless/verify', function (Request $request, RebelEmailOtp $otp, KeyedHasher $hasher) {
    $request->validate(['code' => ['required', 'string']]);
    $s = $request->session()->get('demo_pwl');
    if (! is_array($s)) {
        return redirect()->route('demo.passwordless');
    }

    $purpose = 'demo-passwordless-login';
    $context = SecurityContext::fromRequest($request, $hasher)->withPurpose($purpose);
    $result = $otp->verify($s['challenge_id'], $request->string('code')->toString(), $context);

    if (! $result->success) {
        return back()->withErrors(['code' => 'Invalid or expired code — check your Mailtrap inbox.']);
    }

    $user = User::where('email', $s['email'])->first();
    $request->session()->forget('demo_pwl');

    if ($user === null) {
        // Anti-enumeration: the OTP verified, but no demo account exists for that address.
        return redirect('/')->with('status', 'Code verified, but no demo account exists for that email. Try admin@demo.test or demo.customer@example.com.');
    }

    Auth::login($user);

    return redirect('/')->with('status', 'Signed in passwordlessly as '.$user->email.'.');
})->name('demo.passwordless.verify.submit');

/*
|--------------------------------------------------------------------------
| Step-up (laravel-rebel-step-up) — interactive
|--------------------------------------------------------------------------
| A "sensitive action" protected by the step-up middleware. If you have no
| valid confirmation, the middleware redirects to the challenge screen, which
| issues an email-OTP step-up (delivered to Mailtrap) you must confirm.
*/

Route::middleware(['web', 'auth'])->group(function (): void {
    // The sensitive action. Reaching it proves a valid step-up confirmation exists.
    Route::get('/demo/secure-action', fn () => view('demo.secure-action'))
        ->middleware('rebel.stepup:change-email')
        ->name('demo.secure-action');

    // The step-up challenge screen (config rebel-step-up.redirect_route points here).
    Route::get('/demo/stepup/{purpose}', function (Request $request, RebelStepUp $stepUp, KeyedHasher $hasher, string $purpose) {
        $context = new StepUpContext(
            $request->user(),
            $purpose,
            SecurityContext::fromRequest($request, $hasher)->withPurpose($purpose),
        );

        $result = $stepUp->start($context); // email-OTP step-up -> sends a code to Mailtrap

        $request->session()->put('demo_stepup', [
            'purpose' => $purpose,
            'challenge_id' => $result->challengeId,
        ]);

        return view('demo.stepup', [
            'purpose' => $purpose,
            'driver' => $result->driver,
        ]);
    })->name('demo.stepup.show');

    Route::post('/demo/stepup/{purpose}', function (Request $request, RebelStepUp $stepUp, KeyedHasher $hasher, string $purpose) {
        $request->validate(['code' => ['required', 'string']]);
        $session = $request->session()->get('demo_stepup');

        if (! is_array($session) || ($session['purpose'] ?? null) !== $purpose) {
            return redirect()->route('demo.stepup.show', $purpose);
        }

        $context = new StepUpContext(
            $request->user(),
            $purpose,
            SecurityContext::fromRequest($request, $hasher)->withPurpose($purpose),
        );

        $result = $stepUp->confirm($session['challenge_id'], $request->string('code')->toString(), $context);

        if (! $result->success) {
            return back()->withErrors(['code' => 'Invalid or expired code — check your Mailtrap inbox.']);
        }

        $request->session()->forget('demo_stepup');

        return redirect()->route('demo.secure-action');
    })->name('demo.stepup.confirm');
});

/*
|--------------------------------------------------------------------------
| Driver / channel try-outs (REAL provider APIs)
|--------------------------------------------------------------------------
| One flow per delivery driver we ship. Each sends a REAL verification through
| the laravel-rebel-channels router, which records the send/verify on the audit
| trail (channel + provider) so it shows up in the admin panel's Channel
| Performance & Provider Health. Twilio first; add more drivers the same way.
*/

Route::get('/demo/twilio', fn () => view('demo.twilio-start', [
    'phone' => (string) (config('app.twilio_test_phone') ?? env('TWILIO_TEST_PHONE', '')),
    'configured' => env('TWILIO_ACCOUNT_SID') !== null && env('TWILIO_VERIFY_SERVICE_SID') !== null,
]))->name('demo.twilio');

Route::post('/demo/twilio/start', function (Request $request, VerificationRouter $router, KeyedHasher $hasher) {
    $request->validate(['phone' => ['required', 'string']]);
    $phone = $request->string('phone')->toString();
    $context = SecurityContext::fromRequest($request, $hasher)->withPurpose('demo-twilio-sms');

    $result = $router->start(PhoneIdentifier::from($phone), Channel::Sms, $context);

    if ($result->failed()) {
        return back()->withErrors(['phone' => 'Could not send: '.($result->reason ?? 'provider error').'. Check the TWILIO_* values in .env.']);
    }

    $request->session()->put('demo_twilio', ['phone' => $phone, 'reference' => $result->reference, 'provider' => $result->provider]);

    return redirect()->route('demo.twilio.verify');
})->name('demo.twilio.start');

Route::get('/demo/twilio/verify', function (Request $request) {
    $s = $request->session()->get('demo_twilio');

    return is_array($s) ? view('demo.twilio-verify', ['provider' => $s['provider'] ?? 'twilio']) : redirect()->route('demo.twilio');
})->name('demo.twilio.verify');

Route::post('/demo/twilio/verify', function (Request $request, VerificationRouter $router, KeyedHasher $hasher) {
    $request->validate(['code' => ['required', 'string']]);
    $s = $request->session()->get('demo_twilio');
    if (! is_array($s) || ! is_string($s['reference'] ?? null)) {
        return redirect()->route('demo.twilio');
    }

    $context = SecurityContext::fromRequest($request, $hasher)->withPurpose('demo-twilio-sms');
    $result = $router->check(PhoneIdentifier::from($s['phone']), $request->string('code')->toString(), $s['reference'], $context);

    if (! $result->approved()) {
        return back()->withErrors(['code' => 'Invalid or expired code.']);
    }

    $request->session()->forget('demo_twilio');

    return view('demo.twilio-done', ['provider' => $s['provider'] ?? 'twilio']);
})->name('demo.twilio.verify.submit');

/*
|--------------------------------------------------------------------------
| Back-end capability demos (JSON)
|--------------------------------------------------------------------------
*/

/** Recovery codes (laravel-rebel-recovery): single-use, HMAC-hashed backup codes. */
Route::get('/demo/recovery', function (RecoveryCodeManager $manager) {
    $user = User::where('email', 'demo.customer@example.com')->firstOrFail();

    $codes = $manager->generate($user, 10);
    $first = $codes[0] ?? '';
    $firstOk = $first !== '' && $manager->verify($user, $first);
    $firstReuse = $first !== '' && $manager->verify($user, $first);

    return response()->json([
        'package' => 'laravel-rebel-recovery',
        'generated_count' => count($codes),
        'sample_codes' => $codes,
        'first_code_verify' => $firstOk,
        'first_code_reuse_rejected' => ! $firstReuse,
        'remaining' => $manager->remaining($user),
    ], options: JSON_PRETTY_PRINT);
})->name('demo.recovery');

/** Sessions (laravel-rebel-sessions): refresh rotation + reuse detection. */
Route::get('/demo/sessions', function (SessionManager $sessions) {
    $user = User::where('email', 'demo.customer@example.com')->firstOrFail();

    $refresh = $sessions->start($user, SessionType::Refresh, 'demo-device');
    $rotated = $sessions->rotateRefresh($refresh->id, $user);
    $oldReused = $sessions->isRefreshReused($refresh->id);

    return response()->json([
        'package' => 'laravel-rebel-sessions',
        'issued_refresh_id' => $refresh->id,
        'rotated_to_id' => $rotated?->id,
        'old_token_flagged_as_reused' => $oldReused,
        'active_sessions_revoked' => $sessions->revokeAll($user),
    ], options: JSON_PRETTY_PRINT);
})->name('demo.sessions');

/** Step-up policy (laravel-rebel-step-up): inspect a purpose's policy + SCA. */
Route::get('/demo/stepup-policy', function (RebelStepUp $stepUp) {
    $policy = $stepUp->policy('checkout-credit-order');

    return response()->json([
        'package' => 'laravel-rebel-step-up',
        'purpose' => $policy->purpose,
        'required_assurance' => $policy->requiredAssurance->value,
        'drivers' => $policy->drivers,
        'always_require' => $policy->alwaysRequire,
        'sca_dynamic_linking' => $policy->scaDynamicLinking,
    ], options: JSON_PRETTY_PRINT);
})->name('demo.stepup-policy');

/**
 * AI guard (laravel-rebel-ai-guard): SIMULATE an OTP-bombing attack (12 failed
 * verifications against one identifier), then run the deterministic detector and
 * return the anomaly case it opens. Re-runnable (the case is de-duplicated).
 */
Route::get('/demo/ai-guard', function (Request $request, AuditLogger $audit, KeyedHasher $hasher, AnomalyDetector $detector) {
    $identifierHmac = $hasher->hash('attacker@example.com|email')->hash;

    // Forge a burst of failed email-OTP verifications for the same identifier.
    for ($i = 0; $i < 12; $i++) {
        $audit->record(new AuditEvent(
            type: 'email_otp.failed',
            identifierHmac: $identifierHmac,
            keyVersion: 1,
        ));
    }

    $opened = $detector->detect(now()->subDay()->toImmutable(), now()->addMinute()->toImmutable());

    $case = AnomalyCase::query()->withoutGlobalScopes()
        ->where('dedupe_key', 'otp_bombing:'.$identifierHmac)->first();

    return response()->json([
        'package' => 'laravel-rebel-ai-guard',
        'simulated' => '12x email_otp.failed for one identifier',
        'cases_opened_or_updated' => $opened,
        'case' => $case === null ? null : [
            'type' => $case->type->value,
            'severity' => $case->severity->value,
            'status' => $case->status->value,
            'events_count' => $case->events_count,
            'signals' => $case->signals,
        ],
    ], options: JSON_PRETTY_PRINT);
})->name('demo.ai-guard');
