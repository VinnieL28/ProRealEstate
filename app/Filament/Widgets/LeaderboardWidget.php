<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\Deal;
use Filament\Widgets\Widget;

class LeaderboardWidget extends Widget
{
    protected static string $view = 'filament.widgets.leaderboard';

    public array $leaders = [];

    public function mount(): void
    {
        $teamId = auth()->user()?->team_id;

        $this->leaders = [
            'leads' => Lead::selectRaw('assigned_to_id, count(*) as total')
                ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
                ->groupBy('assigned_to_id')
                ->with('assignedTo')
                ->orderByDesc('total')
                ->limit(5)
                ->get()
                ->map(fn ($row) => [
                    'name' => $row->assignedTo->name ?? 'Unassigned',
                    'total' => $row->total,
                ])->toArray(),
            'deals' => Deal::selectRaw('lead_id, count(*) as total')
                ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
                ->groupBy('lead_id')
                ->orderByDesc('total')
                ->limit(5)
                ->get()
                ->map(fn ($row) => [
                    'name' => optional($row->lead)->owner_name ?? 'Unknown',
                    'total' => $row->total,
                ])->toArray(),
        ];
    }
}
