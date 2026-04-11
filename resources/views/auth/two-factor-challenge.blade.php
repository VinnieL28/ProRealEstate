<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Verification — {{ config('app.name') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0f172a; color: #e2e8f0; font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1rem; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 2.5rem; width: 100%; max-width: 400px; }
        h1 { font-size: 1.5rem; font-weight: 700; color: #f59e0b; margin-bottom: .5rem; }
        p { color: #94a3b8; font-size: .9rem; line-height: 1.6; margin-bottom: 1.5rem; }
        .icon { font-size: 3rem; margin-bottom: 1rem; text-align: center; }
        label { display: block; font-size: .85rem; font-weight: 600; color: #cbd5e1; margin-bottom: .4rem; }
        input { width: 100%; background: #0f172a; border: 1px solid #334155; border-radius: 6px; padding: .75rem; color: #e2e8f0; font-size: 1.5rem; letter-spacing: .3em; text-align: center; }
        input:focus { outline: none; border-color: #f59e0b; }
        .error { color: #f87171; font-size: .8rem; margin-top: .4rem; }
        button { width: 100%; margin-top: 1.25rem; background: #f59e0b; color: #0f172a; font-weight: 700; padding: .75rem; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; }
        button:hover { background: #d97706; }
    </style>
</head>
<body>
<div class="card">
    <div class="icon">🔐</div>
    <h1>Two-Factor Verification</h1>
    <p>Enter the 6-digit code from your authenticator app to continue.</p>

    <form method="POST" action="{{ route('2fa.verify') }}">
        @csrf
        <label for="otp">Authentication Code</label>
        <input type="text" id="otp" name="otp" maxlength="6" inputmode="numeric" placeholder="000000" autofocus>
        @error('otp') <div class="error">{{ $message }}</div> @enderror
        <button type="submit">Verify</button>
    </form>
</div>
</body>
</html>
