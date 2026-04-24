<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service — ProREI CRM</title>
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
        <h1>Terms of Service</h1>
        <p class="updated">Last updated: {{ date('F j, Y') }}</p>

        <h2>1. Acceptance of Terms</h2>
        <p>By accessing or using ProREI CRM ("Service"), you agree to be bound by these Terms of Service. If you do not agree, do not use the Service.</p>

        <h2>2. Description of Service</h2>
        <p>ProREI CRM is a customer relationship management platform designed for real estate investors. We provide tools for lead management, deal tracking, communications, and workflow automation.</p>

        <h2>3. Account Registration</h2>
        <p>You must provide accurate information when creating an account. You are responsible for maintaining the confidentiality of your credentials and for all activities under your account.</p>

        <h2>4. Free Trial and Billing</h2>
        <p>We offer a 14-day free trial with no credit card required. After the trial, paid subscriptions are billed monthly via Stripe. You may cancel at any time from your billing settings. No refunds are issued for partial months.</p>

        <h2>5. Acceptable Use</h2>
        <p>You agree not to:</p>
        <ul>
            <li>Use the Service for any illegal or unauthorized purpose</li>
            <li>Send spam, unsolicited communications, or violate anti-spam laws (CAN-SPAM, TCPA, GDPR)</li>
            <li>Attempt to reverse-engineer, decompile, or interfere with the Service</li>
            <li>Upload malicious code or attempt to gain unauthorized access</li>
        </ul>

        <h2>6. SMS and Calling Compliance</h2>
        <p>When using SMS and calling features (via Twilio integration), you are solely responsible for compliance with all applicable laws including TCPA, CAN-SPAM, and obtaining proper consent from recipients.</p>

        <h2>7. Data Ownership</h2>
        <p>You retain all rights to the data you upload or create in the Service. We will not access, share, or sell your data to third parties except as required to provide the Service or by law.</p>

        <h2>8. Service Availability</h2>
        <p>We strive for high availability but do not guarantee uninterrupted access. Scheduled maintenance and unforeseen outages may occur.</p>

        <h2>9. Termination</h2>
        <p>We reserve the right to suspend or terminate accounts that violate these Terms. You may terminate your account at any time by contacting support or canceling from your settings.</p>

        <h2>10. Limitation of Liability</h2>
        <p>The Service is provided "as is" without warranties of any kind. To the maximum extent permitted by law, we are not liable for any indirect, incidental, or consequential damages.</p>

        <h2>11. Changes to Terms</h2>
        <p>We may update these Terms from time to time. Continued use after changes constitutes acceptance of the updated Terms.</p>

        <h2>12. Contact</h2>
        <p>For questions about these Terms, contact us at <a href="mailto:support@prorei.example">support@prorei.example</a>.</p>
    </div>
</body>
</html>
