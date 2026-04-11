<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Free Trial — ProREI CRM</title>
    @vite(['resources/css/app.css'])
    <style>
        * { box-sizing: border-box; }
        body { background: #0f172a; color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 0; display: flex; min-height: 100vh; }
        .left { flex: 1; background: linear-gradient(135deg, #1e293b, #0f172a); display: flex; flex-direction: column; justify-content: center; padding: 60px; max-width: 480px; }
        .right { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px; }
        .form-card { background: #1e293b; border: 1px solid #334155; border-radius: 20px; padding: 40px; width: 100%; max-width: 420px; }
        .input { width: 100%; padding: 12px 14px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; color: #f1f5f9; font-size: 0.95rem; outline: none; transition: border-color 0.2s; }
        .input:focus { border-color: #f59e0b; }
        label { display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 6px; }
        .field { margin-bottom: 20px; }
        .btn { width: 100%; background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; padding: 14px; border: none; border-radius: 10px; font-size: 1rem; font-weight: 700; cursor: pointer; }
        .btn:hover { opacity: 0.9; }
        .error-list { background: #450a0a; border: 1px solid #7f1d1d; border-radius: 8px; padding: 12px; margin-bottom: 20px; font-size: 0.85rem; color: #fca5a5; }
        @media (max-width: 768px) { .left { display: none; } }
    </style>
</head>
<body>

    <div class="left">
        <div style="font-size: 1.8rem; font-weight: 900; margin-bottom: 8px;">
            <span style="background: linear-gradient(135deg, #f59e0b, #ef4444); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">ProREI</span>
            <span style="color: #475569;"> CRM</span>
        </div>
        <h2 style="font-size: 2rem; font-weight: 800; line-height: 1.2; margin: 24px 0 16px;">Start your free 14-day trial</h2>
        <p style="color: #64748b; line-height: 1.6; margin-bottom: 40px;">No credit card required. Your team is set up instantly and you can start closing deals today.</p>

        <div style="space-y: 16px;">
            @foreach(['Unlimited leads during trial', 'SMS & Calling via Twilio', 'Gmail integration', 'Deal pipeline & Kanban', 'Team collaboration tools', 'Full onboarding wizard'] as $benefit)
                <div style="display: flex; align-items: center; gap: 10px; padding: 8px 0; color: #94a3b8; font-size: 0.9rem;">
                    <span style="color: #22c55e; font-size: 1rem;">✓</span> {{ $benefit }}
                </div>
            @endforeach
        </div>
    </div>

    <div class="right">
        <div class="form-card">
            <h3 style="font-size: 1.4rem; font-weight: 800; margin: 0 0 8px; color: #f1f5f9;">Create your account</h3>
            <p style="color: #64748b; font-size: 0.85rem; margin: 0 0 28px;">Your company's CRM will be ready in seconds.</p>

            @if($errors->any())
                <div class="error-list">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if(session('success'))
                <div style="background: #052e16; border: 1px solid #166534; border-radius: 8px; padding: 12px; margin-bottom: 20px; color: #86efac; font-size: 0.85rem;">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="/register">
                @csrf
                <div class="field">
                    <label>Company / Investing Entity Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" required
                           class="input" placeholder="e.g. Sunrise REI Holdings">
                </div>
                <div class="field">
                    <label>Your Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="input" placeholder="e.g. John Smith">
                </div>
                <div class="field">
                    <label>Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="input" placeholder="you@company.com">
                </div>
                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password" required minlength="8"
                           class="input" placeholder="Minimum 8 characters">
                </div>
                <div class="field">
                    <label>Confirm Password</label>
                    <input type="password" name="password_confirmation" required
                           class="input">
                </div>
                <button type="submit" class="btn">
                    Create Account &amp; Start Trial →
                </button>
            </form>

            <p style="text-align: center; margin-top: 20px; font-size: 0.85rem; color: #475569;">
                Already have an account? <a href="/admin/login" style="color: #f59e0b; text-decoration: none;">Sign in</a>
            </p>
        </div>
    </div>

</body>
</html>
