<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel Rebel — Extras Demo</title>
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
               background: #0b1020; color: #e6e9f2; line-height: 1.5; }
        .wrap { max-width: 920px; margin: 0 auto; padding: 40px 24px 80px; }
        h1 { font-size: 26px; margin: 0 0 4px; }
        .lead { color: #9aa4bf; margin: 0 0 24px; }
        .back { color: #7dd3fc; font-size: 13px; text-decoration: none; }
        h2 { font-size: 13px; color: #c7d2fe; margin: 28px 0 10px; text-transform: uppercase; letter-spacing: .06em; }
        .tag { display: inline-block; font-size: 11px; color: #7dd3fc; background: #0c2233; padding: 2px 8px;
               border-radius: 999px; margin-bottom: 10px; }
        .pill { font-size: 10.5px; padding: 1px 7px; border-radius: 999px; background: #1f2a44; color: #93c5fd; margin-left: 6px; }
        .pill.ok { background: #0d2e1b; color: #86efac; }
        .pill.warn { background: #2d1d00; color: #fbbf24; }
        .section { padding: 18px; border: 1px solid #1e2740; border-radius: 12px; background: #0e1730; margin-bottom: 16px; }
        .row { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
               padding: 8px 0; border-bottom: 1px solid #1a2340; }
        .row:last-child { border-bottom: 0; padding-bottom: 0; }
        .row .key { font-size: 13px; font-weight: 600; color: #e6e9f2; min-width: 160px; flex-shrink: 0; }
        .row .meta { font-size: 12px; color: #9aa4bf; }
        .row .badges { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
        .badge { font-size: 11px; padding: 2px 8px; border-radius: 6px; border: 1px solid #2a3552; color: #c7d2fe; }
        .badge.aal1 { border-color: #1e4a2a; color: #86efac; background: #0d2e1b; }
        .badge.aal2 { border-color: #1a3a60; color: #93c5fd; background: #0c1f3f; }
        .badge.aal3 { border-color: #4a1e2a; color: #f9a8d4; background: #2d0e1b; }
        .badge.phish { border-color: #4a3010; color: #fcd34d; background: #2d1e08; }
        .badge.notphish { border-color: #1e2740; color: #9aa4bf; background: #0c1326; }
        .badge.amr { border-color: #1a2740; color: #a5b4fc; background: #0c1326; font-size: 10px; }
        .muted { color: #6b7593; font-style: italic; font-size: 12px; }
        .status { padding: 8px 12px; border-radius: 8px; font-size: 13px; margin: 12px 0; }
        .status.pass { background: #0d2e1b; border: 1px solid #1e4a2a; color: #86efac; }
        .status.fail { background: #2d0e1b; border: 1px solid #4a1e2a; color: #fca5a5; }
        form { display: flex; gap: 10px; align-items: center; margin-top: 10px; flex-wrap: wrap; }
        input[type=hidden] {}
        input[type=text] { padding: 8px 12px; border-radius: 8px; border: 1px solid #2a3552;
                background: #0c1326; color: #e6e9f2; font-size: 13px; width: 300px; }
        button { font: inherit; font-size: 13px; padding: 8px 16px; border-radius: 8px; border: none;
                 background: #3b82f6; color: #fff; cursor: pointer; }
        button.sec { background: #1e2740; border: 1px solid #2a3552; }
        code { background: #0c1326; padding: 1px 5px; border-radius: 5px; color: #c7d2fe; font-size: 12px; }
        .err { color: #fca5a5; font-size: 13px; margin-top: 8px; }
        .info { color: #93c5fd; font-size: 12px; margin-top: 6px; }
        .note { color: #fbbf24; font-size: 12px; margin-top: 4px; }
        .turnstile-wrap { margin: 8px 0; }
        footer { margin-top: 36px; color: #6b7593; font-size: 12.5px; }
    </style>
    <!-- Cloudflare Turnstile JS — Cloudflare rotates this file so no static SRI hash is
         published; the canonical loading method per their docs is a direct script tag.
         See: https://developers.cloudflare.com/turnstile/get-started/ -->
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js"
            crossorigin="anonymous"
            async defer></script>
</head>
<body>
    <div class="wrap">
        <span class="tag">padosoft/laravel-rebel-* · extras demo</span>
        <h1>Extras — 9 new packages</h1>
        <p class="lead">Live registry dumps: every bridge driver, delivery channel and verification provider actually registered in this boot.</p>
        <a class="back" href="/">← Back to demo home</a>

        @if(session('extras_status'))
            <div class="status {{ session('extras_ok') ? 'pass' : 'fail' }}" style="margin-top:16px">
                {{ session('extras_status') }}
            </div>
        @endif

        {{-- ─────────────────────────────────────────────────────────────────── --}}
        {{-- 1. STEP-UP DRIVER REGISTRY                                          --}}
        {{-- ─────────────────────────────────────────────────────────────────── --}}
        <h2>Step-up driver registry <span class="pill">{{ count($drivers) }} registered</span></h2>
        <div class="section">
            @forelse($drivers as $driver)
                @php
                    $assurance = $driver->assurance();
                    $aalVal    = $assurance->aal->value ?? (string)$assurance->aal;
                    $aalClass  = strtolower(str_replace('_', '', $aalVal)); // aal1/aal2/aal3
                    $amrList   = implode(', ', $assurance->amr ?? []);
                @endphp
                <div class="row">
                    <span class="key">{{ $driver->key() }}</span>
                    <div class="badges">
                        <span class="badge {{ $aalClass }}">{{ strtoupper($aalVal) }}</span>
                        @if($assurance->phishingResistant)
                            <span class="badge phish">phishing-resistant</span>
                        @else
                            <span class="badge notphish">not phishing-resistant</span>
                        @endif
                        @if($amrList)
                            <span class="badge amr">amr: {{ $amrList }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="muted">No step-up drivers registered — check service providers.</p>
            @endforelse
        </div>

        {{-- ─────────────────────────────────────────────────────────────────── --}}
        {{-- 2. DELIVERY CHANNEL REGISTRY                                        --}}
        {{-- ─────────────────────────────────────────────────────────────────── --}}
        <h2>Delivery channel registry <span class="pill">{{ count($deliveryChannels) }} registered</span></h2>
        <div class="section">
            @forelse($deliveryChannels as $ch)
                <div class="row">
                    <span class="key">{{ $ch->key() }}</span>
                    <div class="meta">
                        supports:
                        @php
                            $chEnum = \Padosoft\Rebel\Channels\Enums\Channel::cases();
                            $supported = array_filter($chEnum, fn($c) => $ch->supports($c));
                        @endphp
                        {{ implode(', ', array_map(fn($c) => strtolower($c->name), $supported)) ?: 'unknown' }}
                    </div>
                </div>
            @empty
                <p class="muted">No delivery channels registered — configure TELEGRAM_BOT_TOKEN or DISCORD_WEBHOOK_URL to enable them.</p>
            @endforelse
            @if(count($deliveryChannels) === 0 || !in_array('telegram', array_map(fn($c) => $c->key(), $deliveryChannels)))
                <p class="note">Telegram: set <code>TELEGRAM_BOT_TOKEN</code> in .env to enable.</p>
            @endif
            @if(count($deliveryChannels) === 0 || !in_array('discord', array_map(fn($c) => $c->key(), $deliveryChannels)))
                <p class="note">Discord: set <code>DISCORD_WEBHOOK_URL</code> in .env to enable.</p>
            @endif
        </div>

        {{-- ─────────────────────────────────────────────────────────────────── --}}
        {{-- 3. VERIFICATION PROVIDER REGISTRY                                   --}}
        {{-- ─────────────────────────────────────────────────────────────────── --}}
        <h2>Verification provider registry <span class="pill">{{ count($providers) }} registered</span></h2>
        <div class="section">
            @forelse($providers as $prov)
                <div class="row">
                    <span class="key">{{ $prov->key() }}</span>
                    <div class="meta">
                        supports:
                        @php
                            $chEnum2   = \Padosoft\Rebel\Channels\Enums\Channel::cases();
                            $supported2 = array_filter($chEnum2, fn($c) => $prov->supports($c));
                        @endphp
                        {{ implode(', ', array_map(fn($c) => strtolower($c->name), $supported2)) ?: 'unknown' }}
                    </div>
                </div>
            @empty
                <p class="muted">No verification providers registered.</p>
            @endforelse
            @if(!in_array('vonage', array_map(fn($p) => $p->key(), $providers)))
                <p class="note">Vonage: set <code>VONAGE_API_KEY</code> + <code>VONAGE_API_SECRET</code> in .env to enable.</p>
            @endif
            @if(!in_array('bird', array_map(fn($p) => $p->key(), $providers)))
                <p class="note">Bird: set <code>BIRD_ACCESS_KEY</code> in .env to enable.</p>
            @endif
        </div>

        {{-- ─────────────────────────────────────────────────────────────────── --}}
        {{-- 4. BOT-PROTECTION — TURNSTILE (test keys, always passes)            --}}
        {{-- ─────────────────────────────────────────────────────────────────── --}}
        <h2>Bot-protection — Cloudflare Turnstile <span class="pill ok">test keys active</span></h2>
        <div class="section">
            <p style="font-size:13px; margin:0 0 10px">
                Driver: <code>{{ $botDriver }}</code> |
                Sitekey: <code>{{ $botSiteKey }}</code>
                — Cloudflare's official always-passes test key.
                Submit the form to exercise <code>BotProtection::passes()</code> and see the
                <code>bot.check.passed</code> event in the admin audit log.
            </p>

            @if(isset($botResult))
                <div class="status {{ $botResult ? 'pass' : 'fail' }}">
                    {{ $botResult ? '✓ bot.check.passed — Turnstile token accepted. Event recorded in audit.' : '✗ bot.check.failed — Token rejected.' }}
                </div>
            @endif

            <form method="POST" action="{{ route('demo.extras.bot') }}" id="bot-form">
                @csrf
                {{-- Turnstile renders a hidden cf-turnstile-response field automatically --}}
                <div class="turnstile-wrap"
                     data-sitekey="{{ $botSiteKey }}"
                     data-cf-turnstile></div>
                {{-- Fallback hidden input so the test token is always submitted when JS is off --}}
                <input type="hidden" name="cf-turnstile-response" id="ts-fallback" value="">
                <button type="submit">Verify — check passes()</button>
            </form>
            @if ($errors->has('turnstile'))
                <div class="err">{{ $errors->first('turnstile') }}</div>
            @endif
        </div>

        {{-- ─────────────────────────────────────────────────────────────────── --}}
        {{-- 5. BRIDGE DRIVER END-TO-END (spatie_otp / otpz)                    --}}
        {{-- ─────────────────────────────────────────────────────────────────── --}}
        @auth
        <h2>Bridge step-up — {{ $activeOtpDriver }} end-to-end <span class="pill">sign in to test</span></h2>
        <div class="section">
            <p style="font-size:13px; margin:0 0 10px">
                Trigger a real step-up using the <strong>{{ $activeOtpDriver }}</strong> driver
                (sends an OTP email to <code>{{ auth()->user()->email }}</code> via Mailtrap).
                This proves the bridge activates, the driver emits <code>stepup.{{ $activeOtpDriver }}.started</code>
                in the audit, and <code>verified</code> on correct entry.
            </p>
            @if(!$bridgeDriverAvailable)
                <div class="status fail">
                    Driver <code>{{ $activeOtpDriver }}</code> is not in DriverRegistry — check that the third-party package installed correctly.
                </div>
            @else
                @if(!isset($bridgeStep) || $bridgeStep === 'start')
                    <form method="POST" action="{{ route('demo.extras.bridge.start') }}">
                        @csrf
                        <input type="hidden" name="driver" value="{{ $activeOtpDriver }}">
                        <button type="submit">Start {{ $activeOtpDriver }} step-up → send OTP email</button>
                    </form>
                @elseif($bridgeStep === 'verify')
                    <p class="info">OTP sent! Check your Mailtrap inbox for the code.</p>
                    <form method="POST" action="{{ route('demo.extras.bridge.verify') }}">
                        @csrf
                        <input type="hidden" name="driver" value="{{ $activeOtpDriver }}">
                        <input type="text" name="code" placeholder="Enter code from email" inputmode="numeric"
                               autocomplete="one-time-code" data-testid="bridge-otp-code">
                        <button type="submit">Verify code</button>
                    </form>
                    @if ($errors->has('code'))
                        <div class="err">{{ $errors->first('code') }}</div>
                    @endif
                @elseif($bridgeStep === 'done')
                    <div class="status pass">
                        ✓ {{ $activeOtpDriver }} step-up verified! Events:
                        <code>stepup.{{ $activeOtpDriver }}.started</code> +
                        <code>stepup.{{ $activeOtpDriver }}.verified</code>
                        are in the audit log. <a href="{{ route('demo.extras') }}" style="color:#7dd3fc">Reset</a>
                    </div>
                @endif
            @endif
        </div>
        @else
        <h2>Bridge step-up <span class="pill warn">sign in first</span></h2>
        <div class="section">
            <p class="muted">
                <a href="/demo/login-as-customer" style="color:#7dd3fc">Sign in as demo.customer</a>
                to test the bridge step-up driver end-to-end.
            </p>
        </div>
        @endauth

        <footer>
            New packages: <code>bot-protection</code> · <code>channel-vonage</code> · <code>channel-bird</code> ·
            <code>channel-telegram</code> · <code>channel-discord</code> · <code>bridge-passkeys</code> ·
            <code>bridge-spatie-otp</code> · <code>bridge-laragear-2fa</code> · <code>bridge-otpz</code>
        </footer>
    </div>

    <script>
    // Initialise the Turnstile widget on the cf-turnstile-response div (explicit render).
    // When JS is off, the form falls back to the hidden input (already set to '').
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.querySelector('[data-cf-turnstile]');
        if (el && typeof turnstile !== 'undefined') {
            turnstile.render(el, {
                sitekey: el.dataset.sitekey,
                callback: function(token) {
                    // Fill both the Turnstile-injected field AND our fallback.
                    document.getElementById('ts-fallback').value = token;
                },
            });
        }
    });
    </script>
</body>
</html>
