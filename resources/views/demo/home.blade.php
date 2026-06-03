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
        .wrap { max-width: 920px; margin: 0 auto; padding: 40px 24px 80px; }
        h1 { font-size: 30px; margin: 0 0 4px; }
        .lead { color: #9aa4bf; margin: 0 0 24px; }
        .bar { display: flex; align-items: center; justify-content: space-between; gap: 12px;
               padding: 12px 16px; border: 1px solid #1e2740; border-radius: 10px; background: #0e1730; margin-bottom: 8px; }
        .bar .who { font-size: 14px; }
        .bar .who b { color: #7dd3fc; }
        .btns a, .btns button { font: inherit; font-size: 13px; text-decoration: none; cursor: pointer;
               border: 1px solid #2a3552; background: #16203c; color: #e6e9f2; padding: 7px 12px; border-radius: 8px; margin-left: 6px; }
        .btns a.primary { background: #3b82f6; border-color: #3b82f6; color: #fff; }
        .status { color: #86efac; font-size: 13px; margin: 8px 0 0; }
        h2 { font-size: 15px; color: #c7d2fe; margin: 28px 0 10px; text-transform: uppercase; letter-spacing: .04em; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        a.card { display: block; padding: 16px 18px; border: 1px solid #1e2740; border-radius: 12px;
                 background: #121a30; text-decoration: none; color: inherit; transition: border-color .15s, transform .15s; }
        a.card:hover { border-color: #3b82f6; transform: translateY(-2px); }
        a.card.locked { opacity: .55; }
        a.card h3 { margin: 0 0 6px; font-size: 15px; }
        a.card p { margin: 0; font-size: 12.5px; color: #9aa4bf; }
        .tag { display: inline-block; font-size: 11px; color: #7dd3fc; background: #0c2233; padding: 2px 8px; border-radius: 999px; margin-bottom: 10px; }
        .pill { font-size: 10.5px; padding: 1px 7px; border-radius: 999px; background: #1f2a44; color: #93c5fd; margin-left: 6px; }
        footer { margin-top: 36px; color: #6b7593; font-size: 12.5px; }
        code { background: #0c1326; padding: 2px 6px; border-radius: 6px; color: #c7d2fe; }
    </style>
</head>
<body>
    <div class="wrap">
        <span class="tag">padosoft/laravel-rebel-* · integration demo</span>
        <h1>Laravel Rebel — live demo</h1>
        <p class="lead">Every package in the suite is installed, activated and wired together in this Laravel 13 app.</p>

        <div class="bar">
            @auth
                <div class="who">Signed in as <b>{{ auth()->user()->email }}</b>
                    @if(auth()->user()->is_admin)<span class="pill">admin</span>@endif
                </div>
                <div class="btns">
                    <a href="/demo/secure-action">Try a protected action</a>
                    <a href="/demo/logout">Sign out</a>
                </div>
            @else
                <div class="who">Not signed in — sign in to unlock the protected demos (step-up, admin panel).</div>
                <div class="btns">
                    <a class="primary" href="/login">Sign in (password)</a>
                    <a href="/account/login">Passwordless OTP</a>
                </div>
            @endauth
        </div>
        @if(session('status'))<p class="status">{{ session('status') }}</p>@endif

        <h2>Authentication</h2>
        <div class="grid">
            <a class="card" href="/account/login">
                <h3>Passwordless email-OTP login</h3>
                <p>core + email-otp — enter your email, receive a real code (Mailtrap), verify.</p>
            </a>
            <a class="card" href="/login">
                <h3>Password login (Fortify)</h3>
                <p>bridge-fortify — Fortify's login; its events are mapped into the Rebel audit. Try <code>admin@demo.test</code> / <code>password</code>.</p>
            </a>
        </div>

        <h2>Protected by step-up @guest <span class="pill">sign in first</span> @endguest</h2>
        <div class="grid">
            <a class="card @guest locked @endguest" href="/demo/secure-action">
                <h3>Sensitive action → step-up challenge</h3>
                <p>step-up — guarded by <code>rebel.stepup:change-email</code>. You'll be challenged with an email-OTP step-up (check Mailtrap), then allowed through.</p>
            </a>
            <a class="card @guest locked @endguest" href="/demo/login-as-admin">
                <h3>Web Admin Panel</h3>
                <p>admin + admin-api — the fail-closed security dashboard (signs you in as admin).</p>
            </a>
        </div>

        <h2>Back-end capability demos (JSON)</h2>
        <div class="grid">
            <a class="card" href="/demo/recovery">
                <h3>Recovery codes</h3>
                <p>recovery — 10 single-use codes; verify once, reuse rejected.</p>
            </a>
            <a class="card" href="/demo/sessions">
                <h3>Sessions &amp; refresh rotation</h3>
                <p>sessions — rotate a refresh token, detect reuse (theft signal).</p>
            </a>
            <a class="card" href="/demo/stepup-policy">
                <h3>Step-up policy + PSD2/SCA</h3>
                <p>step-up — the checkout policy with SCA dynamic linking on.</p>
            </a>
            <a class="card" href="/demo/ai-guard">
                <h3>AI guard — simulate an attack</h3>
                <p>ai-guard — forges an OTP-bombing burst, runs the detector, returns the anomaly case.</p>
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
