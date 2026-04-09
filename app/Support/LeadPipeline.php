<?php

namespace App\Support;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LeadPipeline
{
    public const STAGES = [
        'new_lead' => 'New Leads',
        'no_contact' => 'No Contact Made',
        'contact_made' => 'Contact Made',
        'appointment_set' => 'Appointments Set',
        'due_diligence' => 'Due Diligence',
        'offer_made' => 'Offers Made',
        'under_contract' => 'Under Contract',
        'closed_won' => 'Closed Won',
        'closed_lost' => 'Closed Lost',
    ];

    public const WARM_STAGES = [
        'contact_made',
        'appointment_set',
        'due_diligence',
        'offer_made',
        'under_contract',
    ];

    public static function stages(): array
    {
        return self::STAGES;
    }

    public static function warmStages(): array
    {
        return self::WARM_STAGES;
    }

    /**
     * Build an ordered board of leads keyed by pipeline stage.
     *
     * @param  callable|null  $constraint receives the query instance for extra scopes.
     * @param  array<int, string>|null  $stages desired order of columns.
     */
    public static function build(?callable $constraint = null, ?array $stages = null): Collection
    {
        $stages = $stages ?: array_keys(self::stages());
        $board = collect();

        foreach ($stages as $stageKey) {
            $board[$stageKey] = collect();
        }

        /** @var Builder $query */
        $query = Lead::query()->latest('updated_at');
        if ($constraint) {
            $constraint($query);
        }

        $defaultStage = $stages[0] ?? 'new_lead';

        $query->get()->each(function (Lead $lead) use (&$board, $defaultStage) {
            $stage = $lead->stage ?: $defaultStage;
            if (! $board->has($stage)) {
                $stage = $defaultStage;
            }

            $board[$stage] = $board[$stage]->push(self::present($lead));
        });

        return $board;
    }

    public static function present(Lead $lead): array
    {
        $created = $lead->created_at;
        $updated = $lead->updated_at;

        return [
            'id' => $lead->id,
            'name' => $lead->owner_name ?? 'Unnamed Lead',
            'email' => $lead->email ?? 'No email',
            'phone' => $lead->phone ?? 'No phone',
            'status' => $lead->stage ?? 'Open',
            'source' => $lead->lead_source ?: 'No Source',
            'notes' => $lead->notes ? Str::limit($lead->notes, 48) : null,
            'created' => $created ? $created->format('M d, Y') : 'N/A',
            'updated' => $updated ? $updated->format('M d, Y \a\t g:i a') : 'No updates yet',
            'days_in_pipeline' => $created ? now()->diffInDays($created) . ' days' : 'N/A',
            'property_address' => $lead->property_address ?? '—',
            'beds_baths' => $lead->beds_baths ?? '—',
            'campaign_name' => $lead->campaign_name ?? '—',
            'team_assigned' => $lead->team_assigned ?? '—',
            'pending_tasks' => $lead->pending_tasks ?? '—',
            'tags' => $lead->tags ?? '—',
            'communications' => $lead->communications ?? '—',
            'last_outgoing_touch' => $lead->last_outgoing_touch ?? '—',
            'last_incoming_touch' => $lead->last_incoming_touch ?? '—',
            'last_offer_info' => $lead->last_offer_info ?? '—',
            'uc_date' => $lead->uc_date ?? '—',
            'uc_price' => $lead->uc_price ?? '—',
            'sch_closing_date' => $lead->sch_closing_date ?? '—',
        ];
    }
}
