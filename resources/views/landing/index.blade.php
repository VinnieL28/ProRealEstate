<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pro Real Estate CRM — Close More Deals</title>
    <meta name="description" content="The all-in-one CRM built for real estate investors. Manage leads, deals, pipelines, and communications in one place.">
    @vite(['resources/css/app.css'])
    <style>
        * { box-sizing: border-box; }
        body { background: #0f172a; color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 0; }
        .gradient-text { background: linear-gradient(135deg, #f59e0b, #ef4444); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 16px; padding: 28px; }
        .btn-primary { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; padding: 14px 32px; border-radius: 10px; font-weight: 700; text-decoration: none; display: inline-block; transition: opacity 0.2s; }
        .btn-primary:hover { opacity: 0.9; }
        .btn-outline { border: 2px solid #475569; color: #cbd5e1; padding: 12px 28px; border-radius: 10px; font-weight: 600; text-decoration: none; display: inline-block; transition: all 0.2s; }
        .btn-outline:hover { border-color: #f59e0b; color: #f59e0b; }
        nav { display: flex; align-items: center; justify-content: space-between; max-width: 1200px; margin: 0 auto; padding: 20px 24px; }
        section { max-width: 1200px; margin: 0 auto; padding: 80px 24px; }
        .feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }
        .price-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; }
        .check { color: #22c55e; margin-right: 8px; }
    </style>
</head>
<body>

    {{-- Navigation --}}
    <header style="background: rgba(15,23,42,0.95); backdrop-filter: blur(10px); position: sticky; top: 0; z-index: 100; border-bottom: 1px solid #1e293b;">
        <nav>
            <div style="font-size: 1.4rem; font-weight: 800; letter-spacing: -0.5px;">
                <span class="gradient-text">ProREI</span> <span style="color: #64748b; font-weight: 400;">CRM</span>
            </div>
            <div style="display: flex; gap: 16px; align-items: center;">
                <a href="#features" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem;">Features</a>
                <a href="#pricing" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem;">Pricing</a>
                <a href="/admin/login" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem;">Login</a>
                <a href="/register" class="btn-primary" style="padding: 8px 20px; font-size: 0.85rem;">Start Free Trial</a>
            </div>
        </nav>
    </header>

    {{-- Hero --}}
    <section style="text-align: center; padding-top: 120px; padding-bottom: 100px;">
        <div style="display: inline-block; background: #1e293b; border: 1px solid #334155; border-radius: 999px; padding: 6px 18px; font-size: 0.8rem; color: #f59e0b; margin-bottom: 24px; font-weight: 600;">
            🚀 14-Day Free Trial — No Credit Card Required
        </div>
        <h1 style="font-size: clamp(2.5rem, 6vw, 4.5rem); font-weight: 900; line-height: 1.1; margin: 0 0 24px;">
            Close More Real Estate Deals<br><span class="gradient-text">With Less Effort</span>
        </h1>
        <p style="font-size: 1.2rem; color: #94a3b8; max-width: 600px; margin: 0 auto 48px; line-height: 1.6;">
            The all-in-one CRM built specifically for real estate investors. Manage leads, deals, pipelines, SMS, calling, and documents in one place.
        </p>
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <a href="/register" class="btn-primary" style="font-size: 1.1rem; padding: 16px 40px;">
                Start Your Free Trial →
            </a>
            <a href="/admin/login" class="btn-outline" style="font-size: 1.1rem; padding: 16px 40px;">
                Sign In
            </a>
        </div>

        {{-- Stats --}}
        <div style="display: flex; justify-content: center; gap: 60px; margin-top: 80px; flex-wrap: wrap;">
            @foreach(['500+ Active Users' => '🏠', '50K+ Leads Tracked' => '📊', '$2M+ in Deals Closed' => '💰'] as $stat => $icon)
                <div>
                    <div style="font-size: 1.1rem;">{{ $icon }} <strong style="color: #f1f5f9;">{{ $stat }}</strong></div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Features --}}
    <section id="features">
        <h2 style="text-align: center; font-size: 2.5rem; font-weight: 800; margin-bottom: 16px;">Everything You Need to Scale</h2>
        <p style="text-align: center; color: #64748b; margin-bottom: 60px; font-size: 1.05rem;">Built by investors, for investors. Every feature was designed around how you actually work deals.</p>
        <div class="feature-grid">
            @foreach([
                ['icon' => '📱', 'title' => 'Twilio SMS & Calling', 'desc' => 'Send SMS and initiate calls directly from lead profiles. Full conversation threads, inbound webhooks, and call logging with duration tracking.'],
                ['icon' => '📋', 'title' => 'Lead Pipeline Kanban', 'desc' => 'Drag-and-drop leads through your custom stages. Color-coded by lead score, quick action buttons, and last-contact timestamps.'],
                ['icon' => '🏘️', 'title' => 'Property Matching', 'desc' => 'Automatically match properties to buyer leads by budget and location. Manual linking and auto-linking with one click.'],
                ['icon' => '💼', 'title' => 'Deal Tracking', 'desc' => 'Full deal pipeline with Kanban view, days-in-stage warnings, profit calculations, and document generation.'],
                ['icon' => '📧', 'title' => 'Gmail Integration', 'desc' => 'Connect your Gmail account to send emails from lead profiles, pull inbox by lead email, and keep full email threads.'],
                ['icon' => '📊', 'title' => 'Analytics & Reports', 'desc' => 'Dashboard stats, KPI tracking, leaderboards, lead source analysis, and deal conversion reporting.'],
                ['icon' => '🤖', 'title' => 'Workflow Automation', 'desc' => 'Auto-create tasks, send notifications, and update stages based on triggers. Save hours of manual work every week.'],
                ['icon' => '👥', 'title' => 'Team Management', 'desc' => 'Role-based access control, team invitations, performance leaderboards, and per-user activity tracking.'],
            ] as $feature)
                <div class="card">
                    <div style="font-size: 2rem; margin-bottom: 12px;">{{ $feature['icon'] }}</div>
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0 0 8px; color: #f1f5f9;">{{ $feature['title'] }}</h3>
                    <p style="color: #64748b; font-size: 0.9rem; line-height: 1.6; margin: 0;">{{ $feature['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Pricing --}}
    <section id="pricing" style="background: #0f172a;">
        <h2 style="text-align: center; font-size: 2.5rem; font-weight: 800; margin-bottom: 16px;">Simple, Transparent Pricing</h2>
        <p style="text-align: center; color: #64748b; margin-bottom: 60px; font-size: 1.05rem;">All plans include a 14-day free trial. No credit card required.</p>
        <div class="price-grid" style="max-width: 960px; margin: 0 auto;">
            @foreach([
                ['name' => 'Starter', 'price' => 29, 'badge' => '', 'features' => ['Up to 3 agents', '500 leads', 'SMS & Calling', 'Gmail Integration', 'Basic Analytics']],
                ['name' => 'Pro',     'price' => 79, 'badge' => 'Most Popular', 'features' => ['Up to 10 agents', '5,000 leads', 'SMS & Calling', 'Gmail Integration', 'Advanced Analytics', 'Workflow Automation']],
                ['name' => 'Enterprise', 'price' => 199, 'badge' => '', 'features' => ['Unlimited agents', 'Unlimited leads', 'SMS & Calling', 'Gmail Integration', 'Advanced Analytics', 'Workflow Automation', 'Priority Support', 'Custom Integrations']],
            ] as $plan)
                <div style="background: #1e293b; border: 2px solid {{ $plan['badge'] ? '#f59e0b' : '#334155' }}; border-radius: 20px; padding: 36px; position: relative; display: flex; flex-direction: column;">
                    @if($plan['badge'])
                        <div style="position: absolute; top: -14px; left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; font-size: 0.75rem; font-weight: 700; padding: 4px 16px; border-radius: 999px;">
                            {{ $plan['badge'] }}
                        </div>
                    @endif
                    <div style="font-size: 1.4rem; font-weight: 800; color: #f1f5f9;">{{ $plan['name'] }}</div>
                    <div style="font-size: 3rem; font-weight: 900; color: #f1f5f9; margin: 12px 0;">
                        ${{ $plan['price'] }}<span style="font-size: 1rem; font-weight: 400; color: #64748b;">/month</span>
                    </div>
                    <ul style="list-style: none; padding: 0; margin: 20px 0 32px; flex: 1; space-y: 8px;">
                        @foreach($plan['features'] as $f)
                            <li style="padding: 6px 0; font-size: 0.9rem; color: #cbd5e1;">
                                <span class="check">✓</span>{{ $f }}
                            </li>
                        @endforeach
                    </ul>
                    <a href="/register" class="{{ $plan['badge'] ? 'btn-primary' : 'btn-outline' }}" style="text-align: center; display: block;">
                        Start Free Trial
                    </a>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Testimonials --}}
    <section>
        <h2 style="text-align: center; font-size: 2.5rem; font-weight: 800; margin-bottom: 60px;">What Investors Are Saying</h2>
        <div class="feature-grid" style="max-width: 900px; margin: 0 auto;">
            @foreach([
                ['quote' => 'We went from managing leads in 5 different spreadsheets to having everything in one place. Our conversion rate is up 40%.', 'name' => 'Marcus T.', 'title' => 'Wholesaler, Dallas TX'],
                ['quote' => 'The Twilio integration is a game-changer. I can text and call leads directly from the CRM and it logs everything automatically.', 'name' => 'Sarah K.', 'title' => 'Acquisition Manager, Phoenix AZ'],
                ['quote' => 'Finally a CRM that understands real estate investing. The deal pipeline kanban with profit tracking is exactly what I needed.', 'name' => 'James R.', 'title' => 'Fix & Flip Investor, Atlanta GA'],
            ] as $t)
                <div class="card" style="position: relative;">
                    <div style="font-size: 2rem; color: #f59e0b; margin-bottom: 12px;">"</div>
                    <p style="color: #94a3b8; font-size: 0.95rem; line-height: 1.7; margin: 0 0 20px;">{{ $t['quote'] }}</p>
                    <div>
                        <div style="font-weight: 700; color: #f1f5f9;">{{ $t['name'] }}</div>
                        <div style="color: #64748b; font-size: 0.85rem;">{{ $t['title'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- CTA --}}
    <section style="text-align: center; background: linear-gradient(135deg, #1e293b, #0f172a); border-radius: 24px; margin: 0 24px 80px; padding: 80px 40px;">
        <h2 style="font-size: 2.5rem; font-weight: 900; margin-bottom: 16px;">Ready to Close More Deals?</h2>
        <p style="color: #64748b; font-size: 1.1rem; margin-bottom: 40px;">Join hundreds of investors using ProREI CRM to scale their business.</p>
        <a href="/register" class="btn-primary" style="font-size: 1.15rem; padding: 18px 48px;">
            Start Your Free 14-Day Trial →
        </a>
        <p style="color: #475569; font-size: 0.85rem; margin-top: 16px;">No credit card required. Cancel anytime.</p>
    </section>

    {{-- Footer --}}
    <footer style="border-top: 1px solid #1e293b; padding: 40px 24px; text-align: center; color: #475569; font-size: 0.85rem; max-width: 1200px; margin: 0 auto;">
        <div style="margin-bottom: 16px;">
            <strong style="color: #64748b; font-size: 1.1rem;">ProREI CRM</strong>
        </div>
        <div style="display: flex; justify-content: center; gap: 24px; flex-wrap: wrap; margin-bottom: 16px;">
            <a href="#features" style="color: #475569; text-decoration: none;">Features</a>
            <a href="#pricing" style="color: #475569; text-decoration: none;">Pricing</a>
            <a href="/admin/login" style="color: #475569; text-decoration: none;">Login</a>
            <a href="/register" style="color: #475569; text-decoration: none;">Sign Up</a>
        </div>
        <div>© {{ date('Y') }} Pro Real Estate Investments. All rights reserved.</div>
    </footer>

</body>
</html>
