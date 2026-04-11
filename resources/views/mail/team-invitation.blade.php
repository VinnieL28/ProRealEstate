<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Team Invitation</title></head>
<body style="font-family: sans-serif; background: #f9fafb; padding: 40px 0;">
<div style="max-width: 520px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 40px; border: 1px solid #e5e7eb;">
    <h2 style="color: #111827; margin-top: 0;">You're Invited!</h2>
    <p style="color: #374151;">
        <strong>{{ $invitation->invitedBy->name ?? 'Someone' }}</strong> has invited you to join
        <strong>{{ $invitation->team->name }}</strong> on Pro Real Estate CRM as a
        <strong>{{ ucwords(str_replace('_', ' ', $invitation->role)) }}</strong>.
    </p>
    <p style="color: #374151;">This invitation expires <strong>{{ $invitation->expires_at->format('M d, Y') }}</strong>.</p>

    <a href="{{ $acceptUrl }}"
       style="display: inline-block; background: #f59e0b; color: #fff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: bold; margin: 16px 0;">
        Accept Invitation &amp; Create Account
    </a>

    <p style="color: #9ca3af; font-size: 13px; margin-top: 24px;">
        If you weren't expecting this invitation, you can safely ignore this email.
    </p>
</div>
</body>
</html>
