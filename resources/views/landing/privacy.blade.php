<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy — ProREI CRM</title>
    @vite(['resources/css/app.css'])
    <style>
        body { background: #0f172a; color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 0; line-height: 1.7; }
        .container { max-width: 800px; margin: 0 auto; padding: 60px 24px; }
        h1 { font-size: 2.5rem; margin-bottom: 8px; }
        h2 { font-size: 1.3rem; margin-top: 36px; color: #f59e0b; }
        p, li { color: #cbd5e1; font-size: 0.95rem; }
        a { color: #f59e0b; }
        .updated { color: #64748b; font-size: 0.85rem; margin-bottom: 40px; }
        nav { padding: 20px 24px; border-bottom: 1px solid #1e293b; }
        nav a { color: #94a3b8; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <nav><a href="/">← Back to ProREI CRM</a></nav>
    <div class="container">
        <h1>Privacy Policy</h1>
        <p class="updated">Last updated: {{ date('F j, Y') }}</p>

        <p>ProREI CRM ("we", "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, and safeguard your information.</p>

        <h2>1. Information We Collect</h2>
        <ul>
            <li><strong>Account Information:</strong> Name, email, password (encrypted), phone number, team name</li>
            <li><strong>Payment Information:</strong> Billing details processed securely via Stripe. We do not store credit card numbers</li>
            <li><strong>CRM Data:</strong> Leads, deals, contacts, notes, and other data you upload</li>
            <li><strong>Usage Data:</strong> Login times, IP addresses, browser type, and activity within the Service</li>
            <li><strong>Integration Data:</strong> If you connect Gmail or Twilio, we access only the data you authorize</li>
        </ul>

        <h2>2. How We Use Your Information</h2>
        <ul>
            <li>To provide, maintain, and improve the Service</li>
            <li>To process payments and manage your subscription</li>
            <li>To communicate with you about your account and updates</li>
            <li>To detect and prevent fraud or abuse</li>
            <li>To comply with legal obligations</li>
        </ul>

        <h2>3. Data Sharing</h2>
        <p>We do <strong>not</strong> sell your personal data. We share data only with:</p>
        <ul>
            <li><strong>Service providers</strong> (Stripe for billing, Twilio for SMS, Groq/AI for assistant features) — only as needed to deliver the Service</li>
            <li><strong>Legal authorities</strong> when required by law or to protect our rights</li>
        </ul>

        <h2>4. Data Security</h2>
        <p>We implement industry-standard security measures including:</p>
        <ul>
            <li>HTTPS encryption for all traffic</li>
            <li>Encrypted password storage (bcrypt)</li>
            <li>Multi-tenant data isolation (your team's data is never accessible to other teams)</li>
            <li>Optional two-factor authentication (2FA)</li>
        </ul>

        <h2>5. Data Retention</h2>
        <p>We retain your data for as long as your account is active. Upon account deletion, we delete your data within 30 days, except where retention is required by law.</p>

        <h2>6. Your Rights</h2>
        <p>Depending on your jurisdiction (GDPR, CCPA, etc.), you may have the right to:</p>
        <ul>
            <li>Access the personal data we hold about you</li>
            <li>Correct inaccurate data</li>
            <li>Request deletion of your data</li>
            <li>Export your data (available from the Data Export/Backup page)</li>
            <li>Object to or restrict processing</li>
        </ul>
        <p>To exercise these rights, contact us at <a href="mailto:privacy@prorei.example">privacy@prorei.example</a>.</p>

        <h2>7. Cookies</h2>
        <p>We use essential cookies for authentication and session management. We do not use third-party tracking cookies for advertising.</p>

        <h2>8. Children's Privacy</h2>
        <p>The Service is not intended for individuals under 18. We do not knowingly collect data from minors.</p>

        <h2>9. International Transfers</h2>
        <p>If you access the Service from outside the country where our servers are located, your data may be transferred across borders. We ensure adequate protection through standard contractual clauses where applicable.</p>

        <h2>10. Changes to This Policy</h2>
        <p>We may update this Privacy Policy from time to time. Significant changes will be communicated via email or in-app notification.</p>

        <h2>11. Contact</h2>
        <p>For privacy-related questions, contact us at <a href="mailto:privacy@prorei.example">privacy@prorei.example</a>.</p>
    </div>
</body>
</html>
