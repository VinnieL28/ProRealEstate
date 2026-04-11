<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accept Invitation — Pro Real Estate CRM</title>
    @vite(['resources/css/app.css'])
    <style>
        body { background: #111827; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
    </style>
</head>
<body>
<div style="max-width: 420px; width: 100%; background: #1f2937; border-radius: 16px; padding: 40px; border: 1px solid #374151;">
    <h2 style="color: #f9fafb; margin-top: 0; font-size: 1.5rem;">Join {{ $invitation->team->name }}</h2>
    <p style="color: #9ca3af; font-size: 0.9rem;">
        You've been invited as <strong style="color: #f9fafb;">{{ ucwords(str_replace('_', ' ', $invitation->role)) }}</strong>.
        Create your account below.
    </p>

    @if($errors->any())
        <div style="background: #fee2e2; border: 1px solid #fca5a5; border-radius: 8px; padding: 12px; margin-bottom: 16px; color: #991b1b; font-size: 0.85rem;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('invitation.accept', $invitation->token) }}">
        @csrf
        <div style="margin-bottom: 16px;">
            <label style="color: #d1d5db; font-size: 0.85rem; display: block; margin-bottom: 4px;">Full Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   style="width: 100%; padding: 10px 12px; background: #374151; border: 1px solid #4b5563; border-radius: 8px; color: #f9fafb; font-size: 0.9rem; box-sizing: border-box;">
        </div>
        <div style="margin-bottom: 16px;">
            <label style="color: #d1d5db; font-size: 0.85rem; display: block; margin-bottom: 4px;">Email</label>
            <input type="email" value="{{ $invitation->email }}" disabled
                   style="width: 100%; padding: 10px 12px; background: #1f2937; border: 1px solid #374151; border-radius: 8px; color: #6b7280; font-size: 0.9rem; box-sizing: border-box;">
        </div>
        <div style="margin-bottom: 16px;">
            <label style="color: #d1d5db; font-size: 0.85rem; display: block; margin-bottom: 4px;">Password</label>
            <input type="password" name="password" required minlength="8"
                   style="width: 100%; padding: 10px 12px; background: #374151; border: 1px solid #4b5563; border-radius: 8px; color: #f9fafb; font-size: 0.9rem; box-sizing: border-box;">
        </div>
        <div style="margin-bottom: 24px;">
            <label style="color: #d1d5db; font-size: 0.85rem; display: block; margin-bottom: 4px;">Confirm Password</label>
            <input type="password" name="password_confirmation" required
                   style="width: 100%; padding: 10px 12px; background: #374151; border: 1px solid #4b5563; border-radius: 8px; color: #f9fafb; font-size: 0.9rem; box-sizing: border-box;">
        </div>
        <button type="submit"
                style="width: 100%; background: #f59e0b; color: #fff; padding: 12px; border: none; border-radius: 8px; font-weight: bold; font-size: 1rem; cursor: pointer;">
            Create Account &amp; Join Team
        </button>
    </form>
</div>
</body>
</html>
