<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Padosoft\Rebel\AiGuard\Detection\AnomalyDetector;
use Padosoft\Rebel\Recovery\RecoveryCodeManager;
use Padosoft\Rebel\Sessions\Enums\SessionType;
use Padosoft\Rebel\Sessions\SessionManager;
use Padosoft\Rebel\StepUp\RebelStepUp;

/*
|--------------------------------------------------------------------------
| Laravel Rebel — integration demo routes
|--------------------------------------------------------------------------
| This app wires the WHOLE padosoft/laravel-rebel-* suite together and gives
| each capability a clickable demo so the ecosystem can be exercised end to
| end (front-end + back-end) in a real browser.
*/

Route::view('/', 'demo.home')->name('demo.home');

// Fortify redirects here after a successful password login; send users to the
// demo landing page (the app has no separate dashboard).
Route::get('/home', fn () => redirect('/'))->name('home');

/**
 * Demo helper: authenticate as the seeded admin so the fail-closed admin panel
 * and admin API can be viewed. A real app authenticates via the Rebel flows.
 */
Route::get('/demo/login-as-admin', function () {
    $admin = User::where('email', 'admin@demo.test')->firstOrFail();
    Auth::login($admin);

    return redirect('/admin/rebel');
})->name('demo.login-as-admin');

Route::get('/demo/logout', function () {
    Auth::logout();

    return redirect('/');
})->name('demo.logout');

/**
 * Recovery codes (laravel-rebel-recovery): generate a one-time-shown set for a
 * user, then prove a code verifies once and is then burned (single-use).
 */
Route::get('/demo/recovery', function (RecoveryCodeManager $manager) {
    $user = User::where('email', 'demo.customer@example.com')->firstOrFail();

    $codes = $manager->generate($user, 10);
    $first = $codes[0] ?? '';

    $firstOk = $first !== '' && $manager->verify($user, $first);   // expected: true
    $firstReuse = $first !== '' && $manager->verify($user, $first); // expected: false (burned)

    return response()->json([
        'package' => 'laravel-rebel-recovery',
        'generated_count' => count($codes),
        'sample_codes' => $codes,
        'first_code_verify' => $firstOk,
        'first_code_reuse_rejected' => ! $firstReuse,
        'remaining' => $manager->remaining($user),
    ], options: JSON_PRETTY_PRINT);
})->name('demo.recovery');

/**
 * Sessions (laravel-rebel-sessions): issue a refresh-token session, rotate it,
 * then prove that presenting the OLD (already-rotated) token is flagged as reuse.
 */
Route::get('/demo/sessions', function (SessionManager $sessions) {
    $user = User::where('email', 'demo.customer@example.com')->firstOrFail();

    $refresh = $sessions->start($user, SessionType::Refresh, 'demo-device');
    $rotated = $sessions->rotateRefresh($refresh->id, $user);
    $oldReused = $sessions->isRefreshReused($refresh->id); // expected: true after rotation

    return response()->json([
        'package' => 'laravel-rebel-sessions',
        'issued_refresh_id' => $refresh->id,
        'rotated_to_id' => $rotated?->id,
        'old_token_flagged_as_reused' => $oldReused,
        'active_sessions_revoked' => $sessions->revokeAll($user),
    ], options: JSON_PRETTY_PRINT);
})->name('demo.sessions');

/**
 * Step-up (laravel-rebel-step-up): read the policy for a sensitive action. This
 * proves the purpose registry + assurance + PSD2/SCA dynamic-linking config is live.
 */
Route::get('/demo/stepup', function (RebelStepUp $stepUp) {
    $policy = $stepUp->policy('checkout-credit-order');

    return response()->json([
        'package' => 'laravel-rebel-step-up',
        'purpose' => $policy->purpose,
        'required_assurance' => $policy->requiredAssurance->value,
        'require_phishing_resistant' => $policy->requirePhishingResistant,
        'drivers' => $policy->drivers,
        'always_require' => $policy->alwaysRequire,
        'sca_dynamic_linking' => $policy->scaDynamicLinking, // PSD2 binding amount+payee
    ], options: JSON_PRETTY_PRINT);
})->name('demo.stepup');

/**
 * AI guard (laravel-rebel-ai-guard): run the deterministic anomaly detector over
 * the recent audit window and report how many anomaly cases it raised.
 */
Route::get('/demo/ai-guard', function (AnomalyDetector $detector) {
    $to = now();
    $from = $to->copy()->subDay();

    $cases = $detector->detect($from->toImmutable(), $to->toImmutable());

    return response()->json([
        'package' => 'laravel-rebel-ai-guard',
        'window_from' => $from->toIso8601String(),
        'window_to' => $to->toIso8601String(),
        'anomaly_cases_raised' => $cases,
    ], options: JSON_PRETTY_PRINT);
})->name('demo.ai-guard');
