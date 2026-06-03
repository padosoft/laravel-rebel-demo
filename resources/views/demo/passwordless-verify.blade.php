<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enter your code</title>
    <style>
        :root { color-scheme: dark; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
               background: #0b1020; color: #e6e9f2; display: grid; place-items: center; min-height: 100vh; }
        form { width: 340px; padding: 28px; border: 1px solid #1e2740; border-radius: 14px; background: #121a30; }
        h1 { margin: 0 0 4px; font-size: 20px; }
        p.lead { margin: 0 0 18px; color: #9aa4bf; font-size: 13px; }
        input { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #2a3552; background: #0c1326; color: #e6e9f2; font-size: 18px; letter-spacing: 4px; text-align: center; }
        button { width: 100%; margin-top: 16px; padding: 10px; border: 0; border-radius: 8px; background: #3b82f6; color: #fff; font-weight: 600; cursor: pointer; }
        .err { color: #fca5a5; font-size: 13px; margin-top: 12px; }
        .alt { margin-top: 16px; font-size: 13px; text-align: center; } a { color: #7dd3fc; }
    </style>
</head>
<body>
    <form method="POST" action="{{ route('demo.passwordless.verify.submit') }}">
        @csrf
        <h1>Enter your code</h1>
        <p class="lead">We sent a code to <strong>{{ $masked }}</strong> — check your <strong>Mailtrap</strong> inbox.</p>

        @if ($errors->any())<div class="err">{{ $errors->first() }}</div>@endif

        <input name="code" inputmode="numeric" autocomplete="one-time-code" placeholder="••••••" autofocus data-testid="pwl-code">
        <button type="submit">Verify &amp; sign in</button>
        <p class="alt"><a href="/demo/passwordless">Use a different email</a></p>
    </form>
</body>
</html>
