<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ColdLead;
use App\Models\Contact;
use App\Models\SellerLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadIngestionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => 'required|string|in:fsbo,frbo',
            'seller_name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
            'campaign_name' => 'nullable|string',
            'source' => 'nullable|string',
        ]);

        $nameParts = collect(explode(' ', $data['seller_name'], 2));

        $contact = Contact::firstOrCreate(
            [
                'email' => $data['email'],
                'phone_primary' => $data['phone'],
            ],
            [
                'first_name' => $nameParts->first(),
                'last_name' => $nameParts->last(),
                'address_line1' => $data['address'],
                'city' => $data['city'],
                'state' => $data['state'],
                'postal_code' => $data['postal_code'],
                'status' => $data['status'] ?? 'new',
                'source' => $data['source'] ?? strtoupper($data['type']),
            ]
        );

        $payload = [
            'contact_id' => $contact->id,
            'status' => $data['status'] ?? 'new',
            'campaign_name' => $data['campaign_name'] ?? strtoupper($data['type']),
            'notes' => $data['notes'],
            'drip_status' => 'new',
            'drip_step' => 0,
        ];

        if ($data['type'] === 'fsbo') {
            $payload['property_address'] = collect([
                $data['address'],
                $data['city'],
                $data['state'],
                $data['postal_code'],
            ])->filter()->implode(', ');

            $lead = SellerLead::create($payload);
        } else {
            $lead = ColdLead::create($payload);
        }

        return response()->json([
            'contact_id' => $contact->id,
            'lead_id' => $lead->id,
            'message' => 'Lead ingested',
        ]);
    }
}
