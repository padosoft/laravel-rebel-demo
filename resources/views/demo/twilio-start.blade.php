<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Try Twilio SMS OTP</title>
    <style>
        :root { color-scheme: dark; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
               background: #0b1020; color: #e6e9f2; display: grid; place-items: center; min-height: 100vh; }
        form { width: 360px; padding: 28px; border: 1px solid #1e2740; border-radius: 14px; background: #121a30; }
        h1 { margin: 0 0 4px; font-size: 20px; }
        p.lead { margin: 0 0 18px; color: #9aa4bf; font-size: 13px; }
        label { display: block; font-size: 13px; margin: 12px 0 4px; color: #c7d2fe; }
        input { width: 100%; padding: 9px 11px; border-radius: 8px; border: 1px solid #2a3552; background: #0c1326; color: #e6e9f2; }
        button { width: 100%; margin-top: 18px; padding: 10px; border: 0; border-radius: 8px; background: #3b82f6; color: #fff; font-weight: 600; cursor: pointer; }
        .err { color: #fca5a5; font-size: 13px; margin-top: 12px; }
        .warn { color: #fcd34d; font-size: 13px; margin-top: 12px; }
        .alt { margin-top: 16px; font-size: 13px; text-align: center; } a { color: #7dd3fc; }
        .tag { display:inline-block; font-size:11px; color:#7dd3fc; background:#0c2233; padding:2px 8px; border-radius:999px; margin-bottom:10px; }
    </style>
</head>
<body>
    <form method="POST" action="{{ route('demo.twilio.start') }}">
        @csrf
        <span class="tag">driver · laravel-rebel-channel-twilio</span>
        <h1>Try Twilio SMS OTP</h1>
        <p class="lead">Sends a <strong>real</strong> SMS verification through Twilio Verify via the Rebel
            channels router. The send + verify are recorded on the audit trail (channel <code>sms</code>,
            provider <code>twilio</code>), so they show up in the panel's Channel Performance.</p>

        @unless ($configured)
            <div class="warn">⚠ Twilio isn't configured — set <code>TWILIO_ACCOUNT_SID</code>,
                <code>TWILIO_AUTH_TOKEN</code> and <code>TWILIO_VERIFY_SERVICE_SID</code> in <code>.env</code>.</div>
        @endunless
        @if ($errors->any())<div class="err">{{ $errors->first() }}</div>@endif

        <label for="phone">Phone number (E.164, e.g. +39…)</label>
        <input id="phone" name="phone" type="tel" value="{{ $phone }}" required autofocus>

        <button type="submit">Send SMS code</button>
        <p class="alt"><a href="/">← Back to the demo</a></p>
    </form>
</body>
</html>
