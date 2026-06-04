<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Active driver
    |--------------------------------------------------------------------------
    | Which bot-protection provider backs the BotProtection contract.
    | One of: 'turnstile' (Cloudflare Turnstile), 'recaptcha' (Google reCAPTCHA v3),
    | 'hcaptcha' (hCaptcha) or 'always' (no-op — every request passes, for local dev
    | or an explicit opt-out). An unknown value falls back to 'always'.
    */
    // In this demo we default to Cloudflare's official always-passes TEST keys
    // so the Turnstile widget verifies live and offline without real credentials.
    // sitekey: 1x00000000000000000000AA  |  secret: 1x0000000000000000000000000000000AA
    'driver' => env('REBEL_BOT_DRIVER', 'turnstile'),

    /*
    |--------------------------------------------------------------------------
    | Fail-open vs fail-closed
    |--------------------------------------------------------------------------
    | What happens when the provider cannot be reached or returns an error
    | (network failure, 5xx, malformed JSON, misconfigured secret). The secure
    | default is to FAIL CLOSED (passes() returns false → the request is blocked).
    | Set to true ONLY if availability matters more than bot-resistance for you.
    | Applies to every real provider (turnstile / recaptcha / hcaptcha).
    */
    'fail_open' => env('REBEL_BOT_FAIL_OPEN', false),

    /*
    |--------------------------------------------------------------------------
    | Cloudflare Turnstile
    |--------------------------------------------------------------------------
    | Create a widget at https://dash.cloudflare.com/?to=/:account/turnstile.
    | The "Site Key" goes in your frontend widget; the "Secret Key" is verified
    | server-side here. `endpoint` is overridable for testing/self-hosting.
    */
    // Cloudflare Turnstile test-mode keys:
    //   sitekey 1x00000000000000000000AA  — renders the widget; always passes client-side.
    //   secret  1x0000000000000000000000000000000AA — always returns success server-side.
    // These are Cloudflare's OFFICIAL test keys documented at:
    //   https://developers.cloudflare.com/turnstile/troubleshooting/testing/
    'turnstile' => [
        'secret' => env('TURNSTILE_SECRET_KEY', '1x0000000000000000000000000000000AA'),
        'site_key' => env('TURNSTILE_SITE_KEY', '1x00000000000000000000AA'),
        'endpoint' => env('TURNSTILE_ENDPOINT', 'https://challenges.cloudflare.com/turnstile/v0/siteverify'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google reCAPTCHA v3
    |--------------------------------------------------------------------------
    | Register a v3 key at https://www.google.com/recaptcha/admin. v3 returns a
    | score in [0.0, 1.0] (1.0 = very likely human); a token below `min_score`
    | is treated as a bot. Tune `min_score` per your risk appetite (0.5 default).
    */
    'recaptcha' => [
        'secret' => env('RECAPTCHA_SECRET_KEY'),
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'min_score' => env('RECAPTCHA_MIN_SCORE', 0.5),
        'endpoint' => env('RECAPTCHA_ENDPOINT', 'https://www.google.com/recaptcha/api/siteverify'),
    ],

    /*
    |--------------------------------------------------------------------------
    | hCaptcha
    |--------------------------------------------------------------------------
    | Get keys at https://dashboard.hcaptcha.com. The "Site Key" goes in your
    | frontend widget; the "Secret Key" is verified server-side here.
    */
    'hcaptcha' => [
        'secret' => env('HCAPTCHA_SECRET_KEY'),
        'site_key' => env('HCAPTCHA_SITE_KEY'),
        'endpoint' => env('HCAPTCHA_ENDPOINT', 'https://api.hcaptcha.com/siteverify'),
    ],

];
