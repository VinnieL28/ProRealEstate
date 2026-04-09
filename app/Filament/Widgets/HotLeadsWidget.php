<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class HotLeadsWidget extends Widget
{
    protected static string $view = 'filament.widgets.hot-leads';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public array $leads = [];

    public function mount(): void
    {
        $this->loadLeads();
    }

    public function loadLeads(): void
    {
        $teamId = auth()->user()?->team_id;

        // Hot = motivation_level >= 4, not closed, not touched in 3+ days
        $this->leads = Lead::query()
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->where('motivation_level', '>=', 4)
            ->whereNotIn('stage', ['closed_won', 'closed_lost'])
            ->where('updated_at', '<=', Carbon::now()->subDays(3))
            ->with('assignedTo')
            ->orderByDesc('motivation_level')
            ->orderBy('updated_at')   // oldest touch first = most urgent
            ->limit(15)
            ->get()
            ->map(fn (Lead $lead) => [
                'id'              => $lead->id,
                'name'            => $lead->owner_name
                                     ?: trim($lead->first_name . ' ' . $lead->last_name)
                                     ?: 'Unknown',
                'phone'           => $lead->primary_phone ?? $lead->phone ?? '—',
                'stage'           => $lead->stage,
                'motivation'      => $lead->motivation_level,
                'hot_score'       => $lead->hot_score,
                'sell_timeline'   => $lead->sell_timeline,
                'last_touch'      => Carbon::parse($lead->updated_at)->diffForHumans(),
                'assigned_to'     => $lead->assignedTo?->name ?? 'Unassigned',
                'edit_url'        => route('filament.admin.resources.leads.edit', $lead),
            ])
            ->toArray();
    }
}
