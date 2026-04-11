<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Up Two-Factor Authentication — {{ config('app.name') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0f172a; color: #e2e8f0; font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1rem; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 2.5rem; width: 100%; max-width: 480px; }
        h1 { font-size: 1.5rem; font-weight: 700; color: #f59e0b; margin-bottom: .5rem; }
        p { color: #94a3b8; font-size: .9rem; line-height: 1.6; margin-bottom: 1.5rem; }
        .qr-wrap { background: #fff; display: inline-block; border-radius: 8px; padding: .75rem; margin-bottom: 1.5rem; }
        .qr-wrap img { display: block; width: 200px; height: 200px; }
        .secret-box { background: #0f172a; border: 1px solid #334155; border-radius: 6px; padding: .75rem 1rem; font-family: monospace; font-size: .9rem; letter-spacing: .1em; color: #f59e0b; margin-bottom: 1.5rem; word-break: break-all; }
        label { display: block; font-size: .85rem; font-weight: 600; color: #cbd5e1; margin-bottom: .4rem; }
        input { width: 100%; background: #0f172a; border: 1px solid #334155; border-radius: 6px; padding: .6rem .9rem; color: #e2e8f0; font-size: 1.1rem; letter-spacing: .2em; text-align: center; }
        input:focus { outline: none; border-color: #f59e0b; }
        .error { color: #f87171; font-size: .8rem; margin-top: .4rem; }
        button { width: 100%; margin-top: 1.25rem; background: #f59e0b; color: #0f172a; font-weight: 700; padding: .75rem; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; }
        button:hover { background: #d97706; }
    </style>
</head>
<body>
<div class="card">
    <h1>Two-Factor Authentication</h1>
    <p>Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.), then enter the 6-digit code to confirm.</p>

    <div class="qr-wrap">
        <img src="data:image/svg+xml;base64,{{ $qrCodeSvg }}" alt="QR Code">
    </div>

    <p>Or enter this secret manually:</p>
    <div class="secret-box">{{ $secret }}</div>

    <form method="POST" action="{{ route('2fa.enable') }}">
        @csrf
        <label for="otp">Verification Code</label>
        <input type="text" id="otp" name="otp" maxlength="6" inputmode="numeric" placeholder="000000" autofocus>
        @error('otp') <div class="error">{{ $message }}</div> @enderror
        <button type="submit">Enable 2FA</button>
    </form>
</div>
</body>
</html>
