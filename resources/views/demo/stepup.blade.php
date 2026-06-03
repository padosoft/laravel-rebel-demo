<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Step-up required</title>
    <style>
        :root { color-scheme: dark; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
               background: #0b1020; color: #e6e9f2; display: grid; place-items: center; min-height: 100vh; }
        form, .box { width: 360px; padding: 28px; border: 1px solid #1e2740; border-radius: 14px; background: #121a30; }
        h1 { margin: 0 0 4px; font-size: 20px; }
        p.lead { margin: 0 0 18px; color: #9aa4bf; font-size: 13px; }
        .meta { font-size: 12px; color: #93c5fd; background: #0c1326; border-radius: 8px; padding: 10px 12px; margin-bottom: 16px; }
        input { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #2a3552;
                background: #0c1326; color: #e6e9f2; font-size: 18px; letter-spacing: 4px; text-align: center; }
        button { width: 100%; margin-top: 16px; padding: 10px; border: 0; border-radius: 8px; background: #3b82f6; color: #fff; font-weight: 600; cursor: pointer; }
        .err { color: #fca5a5; font-size: 13px; margin-top: 12px; }
        a { color: #7dd3fc; font-size: 13px; }
    </style>
</head>
<body>
    <form method="POST" action="{{ route('demo.stepup.confirm', $purpose) }}">
        @csrf
        <h1>Step-up required</h1>
        <p class="lead">The action <strong>{{ $purpose }}</strong> needs a fresh confirmation.</p>

        <div class="meta">
            A step-up code was sent via the <strong>{{ $driver }}</strong> driver.<br>
            Open your <strong>Mailtrap</strong> inbox to read it.
        </div>

        @if ($errors->any())
            <div class="err">{{ $errors->first() }}</div>
        @endif

        <input name="code" inputmode="numeric" autocomplete="one-time-code" placeholder="••••••" autofocus
               data-testid="stepup-code">
        <button type="submit">Confirm</button>

        <p style="margin-top:16px"><a href="/">← Cancel</a></p>
    </form>
</body>
</html>
