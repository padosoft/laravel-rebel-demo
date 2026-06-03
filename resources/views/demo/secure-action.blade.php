<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sensitive action unlocked</title>
    <style>
        :root { color-scheme: dark; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
               background: #0b1020; color: #e6e9f2; display: grid; place-items: center; min-height: 100vh; }
        .box { width: 420px; padding: 32px; border: 1px solid #14502a; border-radius: 14px; background: #0e1c14; text-align: center; }
        h1 { margin: 0 0 8px; font-size: 22px; }
        p { color: #9aa4bf; font-size: 14px; }
        .ok { font-size: 40px; }
        a { color: #7dd3fc; font-size: 14px; }
        code { background: #0c1326; padding: 2px 6px; border-radius: 6px; color: #c7d2fe; }
    </style>
</head>
<body>
    <div class="box" data-testid="secure-action-ok">
        <div class="ok">✅</div>
        <h1>Step-up confirmed</h1>
        <p>You passed the <code>change-email</code> step-up, so this sensitive action is now
            allowed for the rest of the confirmation window. A real app would change the email here.</p>
        <p><a href="/">← Back to the demo</a></p>
    </div>
</body>
</html>
