<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use App\Models\CallLog;
use App\Models\SmsLog;
use App\Models\Task;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class TimelineWidget extends Widget
{
    protected static string $view = 'filament.widgets.timeline';

    public ?string $related_type = null;
    public ?int $related_id = null;
    public Collection $items;

    public function loadItems(): void
    {
        $items = collect();
        $teamId = auth()->user()?->team_id;

        $activities = Activity::where('related_type', $this->related_type)
            ->where('related_id', $this->related_id)
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->get()
            ->map(fn ($a) => [
                'type' => $a->type ?? 'activity',
                'time' => $a->created_at,
                'title' => $a->type,
                'body' => $a->description,
            ]);

        $calls = CallLog::where('lead_id', $this->related_type === 'Lead' ? $this->related_id : null)
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->get()
            ->map(fn ($c) => [
                'type' => 'call',
                'time' => $c->called_at,
                'title' => 'Call ' . $c->outcome,
                'body' => 'Duration: ' . ($c->duration_minutes ?? 0) . ' min',
            ]);

        $sms = SmsLog::where('lead_id', $this->related_type === 'Lead' ? $this->related_id : null)
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->get()
            ->map(fn ($s) => [
                'type' => 'sms',
                'time' => $s->sent_at,
                'title' => 'SMS ' . $s->direction,
                'body' => $s->message,
            ]);

        $tasks = Task::where('related_type', $this->related_type)
            ->where('related_id', $this->related_id)
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->get()
            ->map(fn ($t) => [
                'type' => 'task',
                'time' => $t->updated_at ?? $t->created_at,
                'title' => 'Task ' . $t->status,
                'body' => $t->title,
            ]);

        $this->items = $items
            ->merge($activities)
            ->merge($calls)
            ->merge($sms)
            ->merge($tasks)
            ->filter(fn ($i) => !empty($i['time']))
            ->sortByDesc('time')
            ->values();
    }

    public function mount(): void
    {
        $routeName = request()->route()?->getName();
        $recordId = request()->route('record');

        if ($routeName && str_contains($routeName, 'leads')) {
            $this->related_type = 'Lead';
        } elseif ($routeName && str_contains($routeName, 'deals')) {
            $this->related_type = 'Deal';
        } elseif ($routeName && str_contains($routeName, 'properties')) {
            $this->related_type = 'Property';
        }

        $this->related_id = $recordId ? (int) $recordId : null;

        if ($this->related_type && $this->related_id) {
            $this->loadItems();
        } else {
            $this->items = collect();
        }
    }
}
