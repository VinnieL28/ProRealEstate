<?php

namespace App\Filament\Pages;

use App\Models\Activity;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ActivityFeedPage extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-rss';
    protected static ?string $navigationGroup = 'Productivity';
    protected static ?string $navigationLabel = 'Activity Feed';
    protected static ?string $slug            = 'activity-feed';
    protected static string  $view            = 'filament.pages.activity-feed';
    protected static ?int    $navigationSort  = 6;

    public array  $activities = [];
    public string $filter     = 'all';
    public int    $perPage    = 30;

    public function mount(): void
    {
        $this->loadActivities();
    }

    public function loadActivities(): void
    {
        $user  = auth()->user();
        $query = Activity::with('user')
            ->where('team_id', $user?->team_id)
            ->orderByDesc('created_at')
            ->limit($this->perPage);

        if ($this->filter !== 'all') {
            $query->where('type', $this->filter);
        }

        $this->activities = $query->get()->map(fn ($a) => [
            'id'           => $a->id,
            'type'         => $a->type,
            'description'  => $a->description,
            'user'         => $a->user?->name ?? 'System',
            'related_type' => $a->related_type,
            'related_id'   => $a->related_id,
            'created_at'   => $a->created_at?->diffForHumans(),
        ])->toArray();
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->loadActivities();
    }

    public function loadMore(): void
    {
        $this->perPage += 30;
        $this->loadActivities();
    }
}
