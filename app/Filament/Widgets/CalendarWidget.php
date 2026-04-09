<?php

namespace App\Filament\Widgets;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Task;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class CalendarWidget extends Widget
{
    protected static string $view = 'filament.widgets.calendar-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 99;

    /**
     * Return events as a JSON-serialisable array for FullCalendar.
     */
    public function getEvents(): array
    {
        $teamId = auth()->user()?->team_id;
        $events = [];

        // Tasks — colour by priority
        $priorityColors = [
            'high'   => '#ef4444',
            'medium' => '#f59e0b',
            'low'    => '#6b7280',
        ];

        Task::query()
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->where('status', '!=', 'done')
            ->whereNotNull('due_date')
            ->with('assignedTo')
            ->get()
            ->each(function (Task $task) use (&$events, $priorityColors) {
                $events[] = [
                    'id'              => 'task-' . $task->id,
                    'title'           => '✓ ' . $task->title,
                    'start'           => Carbon::parse($task->due_date)->toIso8601String(),
                    'backgroundColor' => $priorityColors[$task->priority] ?? '#6b7280',
                    'borderColor'     => $priorityColors[$task->priority] ?? '#6b7280',
                    'extendedProps'   => [
                        'type'     => 'task',
                        'priority' => $task->priority,
                        'assigned' => $task->assignedTo?->name ?? 'Unassigned',
                        'status'   => $task->status,
                    ],
                ];
            });

        // Deals closing date
        Deal::query()
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->whereNotIn('stage', ['closed_won', 'closed_lost'])
            ->whereNotNull('closing_date')
            ->with('property')
            ->get()
            ->each(function (Deal $deal) use (&$events) {
                $events[] = [
                    'id'              => 'deal-' . $deal->id,
                    'title'           => '$ ' . $deal->name,
                    'start'           => Carbon::parse($deal->closing_date)->toIso8601String(),
                    'backgroundColor' => '#8b5cf6',
                    'borderColor'     => '#8b5cf6',
                    'extendedProps'   => [
                        'type'     => 'deal',
                        'stage'    => $deal->stage,
                        'property' => $deal->property?->address ?? '—',
                    ],
                ];
            });

        // Leads with appointment_set stage — use updated_at as approximate appointment date
        Lead::query()
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->where('stage', 'appointment_set')
            ->get()
            ->each(function (Lead $lead) use (&$events) {
                $events[] = [
                    'id'              => 'appt-' . $lead->id,
                    'title'           => '📅 ' . ($lead->owner_name ?: ($lead->first_name . ' ' . $lead->last_name)),
                    'start'           => Carbon::parse($lead->updated_at)->toDateString(),
                    'backgroundColor' => '#10b981',
                    'borderColor'     => '#10b981',
                    'allDay'          => true,
                    'extendedProps'   => [
                        'type'  => 'appointment',
                        'phone' => $lead->primary_phone ?? $lead->phone ?? '—',
                    ],
                ];
            });

        return $events;
    }
}
