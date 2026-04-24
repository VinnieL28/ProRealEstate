<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LeadDistributionService
{
    /**
     * Round-robin assign the next eligible agent on the team to a lead.
     * Picks the agent who has the least recently assigned lead (or never assigned).
     */
    public function assign(Lead $lead): ?User
    {
        if ($lead->assigned_to_id) {
            return null; // already assigned
        }

        $eligibleRoles = ['real_estate_agent', 'acquisition_manager'];

        $agents = User::where('team_id', $lead->team_id)
            ->whereIn('role', $eligibleRoles)
            ->where('status', 'active')
            ->get();

        if ($agents->isEmpty()) {
            return null;
        }

        // Find the agent whose last assignment is the oldest (round-robin)
        $nextAgent = $agents->sortBy(function (User $agent) use ($lead) {
            $lastAssigned = Lead::where('team_id', $lead->team_id)
                ->where('assigned_to_id', $agent->id)
                ->max('created_at');

            return $lastAssigned ?? '1970-01-01';
        })->first();

        if (!$nextAgent) {
            return null;
        }

        $lead->update(['assigned_to_id' => $nextAgent->id]);

        Log::info("Lead #{$lead->id} auto-assigned to agent {$nextAgent->name} (#{$nextAgent->id}) via round-robin.");

        return $nextAgent;
    }
}
