<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; margin: 40px; line-height: 1.7; }
        h1 { font-size: 20px; text-align: center; margin-bottom: 4px; }
        .subtitle { text-align: center; color: #666; font-size: 11px; margin-bottom: 28px; }
        .section { margin-bottom: 20px; }
        .label { font-weight: bold; color: #555; }
        .divider { border-top: 1px solid #ddd; margin: 20px 0; }
        .signature-block { margin-top: 48px; display: flex; justify-content: space-between; }
        .sig { width: 45%; }
        .sig-line { border-top: 1px solid #222; margin-top: 48px; padding-top: 4px; font-size: 11px; color: #555; }
        .highlight { background: #fffbe6; padding: 10px 14px; border-left: 3px solid #f59e0b; margin: 12px 0; }
    </style>
</head>
<body>
    <h1>Purchase Offer Letter</h1>
    <p class="subtitle">{{ now()->format('F j, Y') }}</p>

    <div class="section">
        <p><span class="label">Seller:</span> {{ $lead->owner_name ?: trim($lead->first_name . ' ' . $lead->last_name) }}</p>
        <p><span class="label">Property:</span> {{ $lead->property_address ?? ($property?->address ?? 'N/A') }}</p>
        <p><span class="label">Buyer / Company:</span> {{ $company ?? 'Pro Real Estate Investments' }}</p>
    </div>

    <div class="divider"></div>

    <div class="section">
        <p>Dear {{ $lead->owner_name ?: 'Seller' }},</p>
        <p>
            We are pleased to present the following offer for the purchase of the above-referenced property.
            This letter outlines the key terms of our proposal. A formal purchase agreement will follow upon
            your acceptance.
        </p>
    </div>

    <div class="highlight">
        <p><span class="label">Offer Price:</span> ${{ number_format($deal?->purchase_price ?? $lead->max_offer ?? 0, 2) }}</p>
        @if($deal?->assignment_fee)
        <p><span class="label">Assignment Fee:</span> ${{ number_format($deal->assignment_fee, 2) }}</p>
        @endif
        <p><span class="label">Contract Type:</span> {{ ucfirst(str_replace('_', ' ', $deal?->contract_type ?? 'Assignment')) }}</p>
        <p><span class="label">Proposed Closing Date:</span> {{ $deal?->closing_date ? $deal->closing_date->format('F j, Y') : 'To be determined' }}</p>
    </div>

    <div class="section">
        <p><span class="label">Key Terms:</span></p>
        <ul>
            <li>All-cash offer, no financing contingency</li>
            <li>As-is purchase — no repair requests after inspection</li>
            <li>Seller to provide clear title at closing</li>
            <li>Closing costs split per standard in {{ $lead->major_market ?? 'your area' }}</li>
        </ul>
    </div>

    <p>
        This offer is contingent upon satisfactory due diligence and is valid for 5 business days.
        Please contact us with any questions.
    </p>

    <div class="signature-block">
        <div class="sig">
            <div class="sig-line">Buyer Signature &amp; Date</div>
        </div>
        <div class="sig">
            <div class="sig-line">Seller Signature &amp; Date</div>
        </div>
    </div>
</body>
</html>
