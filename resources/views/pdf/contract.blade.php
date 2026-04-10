<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; margin: 36px; line-height: 1.65; }
        h1 { font-size: 17px; text-align: center; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; }
        h2 { font-size: 13px; margin-top: 20px; margin-bottom: 6px; color: #333; }
        .center { text-align: center; color: #888; font-size: 10px; margin-bottom: 24px; }
        .divider { border-top: 1px solid #ccc; margin: 16px 0; }
        .row { display: flex; gap: 24px; }
        .field { margin-bottom: 8px; }
        .label { font-weight: bold; }
        .sig-block { margin-top: 48px; }
        .sig-row { display: flex; justify-content: space-between; margin-top: 32px; }
        .sig { width: 44%; }
        .sig-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 3px; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <h1>Real Estate Purchase &amp; Sale Agreement</h1>
    <p class="center">Generated {{ now()->format('F j, Y \a\t g:i A') }}</p>

    <div class="divider"></div>

    <h2>1. Parties</h2>
    <div class="field"><span class="label">Seller:</span> {{ $lead->owner_name ?: trim($lead->first_name . ' ' . $lead->last_name) }}</div>
    <div class="field"><span class="label">Seller Address:</span> {{ $lead->owner_mailing_address ?? 'N/A' }}</div>
    <div class="field"><span class="label">Buyer:</span> {{ $company ?? 'Pro Real Estate Investments LLC' }}</div>

    <h2>2. Property</h2>
    <div class="field"><span class="label">Address:</span> {{ $lead->property_address ?? ($property?->address ?? 'N/A') }}</div>
    @if($property)
    <div class="field"><span class="label">Legal Description:</span> {{ $property->parcel_id ?? 'To be provided at closing' }}</div>
    @endif

    <h2>3. Purchase Price &amp; Terms</h2>
    <div class="field"><span class="label">Purchase Price:</span> ${{ number_format($deal?->purchase_price ?? $lead->max_offer ?? 0, 2) }}</div>
    <div class="field"><span class="label">Earnest Money Deposit:</span> $1,000 (due within 3 business days of execution)</div>
    <div class="field"><span class="label">Contract Type:</span> {{ ucfirst(str_replace('_', ' ', $deal?->contract_type ?? 'Assignment')) }}</div>
    @if($deal?->assignment_fee)
    <div class="field"><span class="label">Assignment Fee:</span> ${{ number_format($deal->assignment_fee, 2) }}</div>
    @endif

    <h2>4. Closing</h2>
    <div class="field"><span class="label">Target Closing Date:</span> {{ $deal?->closing_date ? $deal->closing_date->format('F j, Y') : 'On or before 30 days from execution' }}</div>
    <div class="field"><span class="label">Closing Agent:</span> To be designated by Buyer</div>

    <h2>5. Conditions</h2>
    <p>This agreement is subject to: (a) satisfactory inspection during due diligence period of 10 business days;
    (b) clear and marketable title; (c) property delivered vacant at closing (if currently occupied).</p>

    <h2>6. As-Is Sale</h2>
    <p>Buyer accepts property in its current AS-IS condition. Seller makes no representations or warranties as to
    the condition of the property. Buyer has the right to conduct inspections during the due diligence period.</p>

    <div class="sig-block">
        <div class="divider"></div>
        <div class="sig-row">
            <div class="sig">
                <div class="sig-line">Seller Signature</div>
                <div class="sig-line">Printed Name &amp; Date</div>
            </div>
            <div class="sig">
                <div class="sig-line">Buyer Signature</div>
                <div class="sig-line">Printed Name &amp; Date</div>
            </div>
        </div>
    </div>
</body>
</html>
