<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choose a new password — Fortify</title>
    <style>
        :root { color-scheme: dark; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
               background: #0b1020; color: #e6e9f2; display: grid; place-items: center; min-height: 100vh; }
        form { width: 340px; padding: 28px; border: 1px solid #1e2740; border-radius: 14px; background: #121a30; }
        h1 { margin: 0 0 14px; font-size: 20px; }
        label { display: block; font-size: 13px; margin: 12px 0 4px; color: #c7d2fe; }
        input { width: 100%; padding: 9px 11px; border-radius: 8px; border: 1px solid #2a3552; background: #0c1326; color: #e6e9f2; }
        button { width: 100%; margin-top: 18px; padding: 10px; border: 0; border-radius: 8px; background: #3b82f6; color: #fff; font-weight: 600; cursor: pointer; }
        .err { color: #fca5a5; font-size: 13px; margin-top: 12px; }
    </style>
</head>
<body>
    <form method="POST" action="/reset-password">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <h1>Choose a new password</h1>

        @if ($errors->any())<div class="err">{{ $errors->first() }}</div>@endif

        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus>
        <label for="password">New password</label>
        <input id="password" name="password" type="password" required>
        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required>

        <button type="submit">Reset password</button>
    </form>
</body>
</html>
