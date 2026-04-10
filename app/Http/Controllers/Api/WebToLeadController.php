<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebToLeadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'              => 'nullable|string|max:255',
            'first_name'        => 'nullable|string|max:255',
            'last_name'         => 'nullable|string|max:255',
            'phone'             => 'nullable|string|max:30',
            'email'             => 'nullable|email|max:255',
            'source'            => 'nullable|string|max:100',
            'property_interest' => 'nullable|string|max:500',
            'notes'             => 'nullable|string',
            'team_id'           => 'nullable|integer|exists:teams,id',
        ]);

        $teamId = $data['team_id'] ?? null;

        // Auto-assign via round-robin: pick agent with fewest assigned leads
        $assignedTo = $this->roundRobinAgent($teamId);

        $ownerName = $data['name']
            ?? trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''))
            ?: null;

        $lead = Lead::create([
            'team_id'         => $teamId,
            'owner_name'      => $ownerName,
            'first_name'      => $data['first_name'] ?? null,
            'last_name'       => $data['last_name'] ?? null,
            'phone'           => $data['phone'] ?? null,
            'primary_phone'   => $data['phone'] ?? null,
            'email'           => $data['email'] ?? null,
            'primary_email'   => $data['email'] ?? null,
            'lead_source'     => $data['source'] ?? 'Web Form',
            'property_address'=> $data['property_interest'] ?? null,
            'notes'           => $data['notes'] ?? null,
            'stage'           => 'new_lead',
            'assigned_to_id'  => $assignedTo?->id,
        ]);

        return response()->json([
            'success'     => true,
            'lead_id'     => $lead->id,
            'assigned_to' => $assignedTo?->name,
            'message'     => 'Lead captured successfully.',
        ], 201);
    }

    private function roundRobinAgent(?int $teamId): ?User
    {
        $query = User::query()
            ->whereIn('role', ['cold_caller', 'real_estate_agent', 'acquisition_manager', 'lead_manager'])
            ->where('status', 'active')
            ->withCount(['assignedLeads as open_leads_count' => fn ($q) =>
                $q->whereNotIn('stage', ['closed_won', 'closed_lost'])
            ]);

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        return $query->orderBy('open_leads_count')->first();
    }
}
