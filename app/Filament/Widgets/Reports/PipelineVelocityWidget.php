<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Lead;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class PipelineVelocityWidget extends Widget
{
    protected static string $view = 'filament.widgets.reports.pipeline-velocity';

    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    public array $stages = [];

    public function mount(): void
    {
        $teamId = auth()->user()?->team_id;

        $stageOrder = [
            'new_lead'        => 'New Lead',
            'no_contact'      => 'No Contact Made',
            'contact_made'    => 'Contact Made',
            'appointment_set' => 'Appointment Set',
            'due_diligence'   => 'Due Diligence',
            'offer_made'      => 'Offer Made',
            'under_contract'  => 'Under Contract',
            'closed_won'      => 'Closed Won',
            'closed_lost'     => 'Closed Lost',
        ];

        $rows = [];

        foreach ($stageOrder as $stageKey => $stageLabel) {
            $leads = Lead::query()
                ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
                ->where('stage', $stageKey)
                ->get(['created_at', 'updated_at']);

            $count = $leads->count();

            // Avg days since last update (proxy for time spent in current stage)
            $avgDays = $count > 0
                ? round($leads->avg(fn ($l) => Carbon::parse($l->updated_at)->diffInDays(Carbon::now())), 1)
                : null;

            $rows[] = [
                'key'      => $stageKey,
                'label'    => $stageLabel,
                'count'    => $count,
                'avg_days' => $avgDays,
                // Heat: > 14 days is slow, < 3 days is fast
                'heat'     => $avgDays === null ? 'none'
                    : ($avgDays > 14 ? 'slow' : ($avgDays > 7 ? 'medium' : 'fast')),
            ];
        }

        $this->stages = $rows;
    }
}
