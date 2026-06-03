<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel Rebel — Integration Demo</title>
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
               background: #0b1020; color: #e6e9f2; line-height: 1.5; }
        .wrap { max-width: 860px; margin: 0 auto; padding: 48px 24px 80px; }
        h1 { font-size: 30px; margin: 0 0 4px; }
        .lead { color: #9aa4bf; margin: 0 0 32px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        a.card { display: block; padding: 18px 20px; border: 1px solid #1e2740; border-radius: 12px;
                 background: #121a30; text-decoration: none; color: inherit; transition: border-color .15s, transform .15s; }
        a.card:hover { border-color: #3b82f6; transform: translateY(-2px); }
        a.card h3 { margin: 0 0 6px; font-size: 16px; }
        a.card p { margin: 0; font-size: 13px; color: #9aa4bf; }
        .tag { display: inline-block; font-size: 11px; color: #7dd3fc; background: #0c2233; padding: 2px 8px;
               border-radius: 999px; margin-bottom: 10px; }
        footer { margin-top: 40px; color: #6b7593; font-size: 13px; }
        code { background: #0c1326; padding: 2px 6px; border-radius: 6px; color: #c7d2fe; }
    </style>
</head>
<body>
    <div class="wrap">
        <span class="tag">padosoft/laravel-rebel-* · integration demo</span>
        <h1>Laravel Rebel — live demo</h1>
        <p class="lead">Every package in the suite is installed, activated and wired together in this Laravel 13 app.
            Click through to exercise each capability front-end and back-end.</p>

        <div class="grid">
            <a class="card" href="/account/login" data-testid="demo-otp">
                <h3>→ Passwordless email-OTP login</h3>
                <p>laravel-rebel-core + email-otp — start, receive a code, verify (web).</p>
            </a>
            <a class="card" href="/demo/login-as-admin" data-testid="demo-admin">
                <h3>→ Web Admin Panel</h3>
                <p>laravel-rebel-admin + admin-api — the fail-closed security dashboard.</p>
            </a>
            <a class="card" href="/rebel/admin/api/v1/health" data-testid="demo-health">
                <h3>→ Admin API · health</h3>
                <p>laravel-rebel-admin-api — JSON health endpoint (login as admin first).</p>
            </a>
            <a class="card" href="/demo/recovery" data-testid="demo-recovery">
                <h3>→ Recovery codes</h3>
                <p>laravel-rebel-recovery — single-use, HMAC-hashed backup codes.</p>
            </a>
            <a class="card" href="/demo/sessions" data-testid="demo-sessions">
                <h3>→ Sessions &amp; refresh rotation</h3>
                <p>laravel-rebel-sessions — rotate a refresh token, detect reuse.</p>
            </a>
            <a class="card" href="/login" data-testid="demo-fortify">
                <h3>→ Fortify + Passkeys</h3>
                <p>laravel-rebel-bridge-fortify — step-up drivers over Fortify.</p>
            </a>
        </div>

        <footer>
            Suite: <code>core</code> · <code>email-otp</code> · <code>step-up</code> · <code>bridge-fortify</code> ·
            <code>channels</code> · <code>channel-twilio</code> · <code>admin-api</code> · <code>admin</code> ·
            <code>sessions</code> · <code>recovery</code> · <code>ai-guard</code>
        </footer>
    </div>
</body>
</html>
