<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; margin: 36px; line-height: 1.65; }
        h1 { font-size: 18px; text-align: center; margin-bottom: 2px; }
        .address { text-align: center; font-size: 13px; color: #555; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th { background: #f59e0b; color: #fff; text-align: left; padding: 6px 10px; font-size: 11px; }
        td { padding: 5px 10px; border-bottom: 1px solid #eee; }
        td:first-child { font-weight: bold; color: #444; width: 38%; }
        .section-title { font-size: 13px; font-weight: bold; color: #444; margin-top: 16px; margin-bottom: 4px; border-bottom: 2px solid #f59e0b; padding-bottom: 3px; }
    </style>
</head>
<body>
    <h1>Property Information Sheet</h1>
    <p class="address">{{ $property?->address ?? $lead->property_address ?? 'Address N/A' }}</p>

    <div class="section-title">Property Overview</div>
    <table>
        <tr><td>Status</td><td>{{ ucfirst($property?->status ?? 'Unknown') }}</td></tr>
        <tr><td>Type</td><td>{{ ucfirst(str_replace('_', ' ', $property?->property_type ?? 'N/A')) }}</td></tr>
        <tr><td>Bedrooms</td><td>{{ $property?->bedrooms ?? 'N/A' }}</td></tr>
        <tr><td>Bathrooms</td><td>{{ $property?->bathrooms ?? 'N/A' }}</td></tr>
        <tr><td>Sq Footage</td><td>{{ $property?->sqft ? number_format($property->sqft) . ' sq ft' : 'N/A' }}</td></tr>
        <tr><td>Year Built</td><td>{{ $property?->year_built ?? 'N/A' }}</td></tr>
        <tr><td>Lot Size</td><td>{{ $property?->lot_size ?? 'N/A' }}</td></tr>
    </table>

    <div class="section-title">Financials</div>
    <table>
        <tr><td>ARV</td><td>${{ $property?->arv ? number_format($property->arv, 2) : 'N/A' }}</td></tr>
        <tr><td>Estimated Repairs</td><td>${{ $property?->estimated_repairs ? number_format($property->estimated_repairs, 2) : 'N/A' }}</td></tr>
        <tr><td>Asking Price</td><td>${{ $lead->asking_price ? number_format($lead->asking_price, 2) : 'N/A' }}</td></tr>
        <tr><td>Max Offer</td><td>${{ $lead->max_offer ? number_format($lead->max_offer, 2) : 'N/A' }}</td></tr>
        <tr><td>Annual Taxes</td><td>${{ $lead->annual_taxes ? number_format($lead->annual_taxes, 2) : 'N/A' }}</td></tr>
        <tr><td>Annual Insurance</td><td>${{ $lead->annual_insurance ? number_format($lead->annual_insurance, 2) : 'N/A' }}</td></tr>
    </table>

    <div class="section-title">Lead / Seller Info</div>
    <table>
        <tr><td>Seller</td><td>{{ $lead->owner_name ?: trim($lead->first_name . ' ' . $lead->last_name) }}</td></tr>
        <tr><td>Phone</td><td>{{ $lead->primary_phone ?? $lead->phone ?? 'N/A' }}</td></tr>
        <tr><td>Email</td><td>{{ $lead->primary_email ?? $lead->email ?? 'N/A' }}</td></tr>
        <tr><td>Motivation</td><td>{{ $lead->motivation_level ?? 'N/A' }} / 5</td></tr>
        <tr><td>Timeline</td><td>{{ $lead->sell_timeline ? ucfirst(str_replace('_', ' ', $lead->sell_timeline)) : 'N/A' }}</td></tr>
        <tr><td>Occupancy</td><td>{{ ucfirst($lead->occupancy_status ?? 'N/A') }}</td></tr>
    </table>

    <p style="font-size:10px;color:#aaa;text-align:center;margin-top:24px;">
        Generated {{ now()->format('F j, Y') }} — Pro Real Estate CRM
    </p>
</body>
</html>
