<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-factor challenge — Fortify</title>
    <style>
        :root { color-scheme: dark; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
               background: #0b1020; color: #e6e9f2; display: grid; place-items: center; min-height: 100vh; }
        form { width: 320px; padding: 28px; border: 1px solid #1e2740; border-radius: 14px; background: #121a30; }
        h1 { margin: 0 0 16px; font-size: 20px; }
        input { width: 100%; padding: 9px 11px; border-radius: 8px; border: 1px solid #2a3552;
                background: #0c1326; color: #e6e9f2; }
        button { width: 100%; margin-top: 16px; padding: 10px; border: 0; border-radius: 8px;
                 background: #3b82f6; color: #fff; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body>
    <form method="POST" action="/two-factor-challenge">
        @csrf
        <h1>Two-factor challenge</h1>
        <input name="code" inputmode="numeric" autocomplete="one-time-code" placeholder="Authentication code" autofocus>
        <button type="submit">Verify</button>
    </form>
</body>
</html>
