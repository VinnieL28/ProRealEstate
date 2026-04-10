<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use App\Models\CallLog;
use App\Models\EmailLog;
use App\Models\Lead;
use App\Models\SmsLog;
use App\Models\Task;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class LeadTimelineWidget extends Widget
{
    protected static string $view = 'filament.widgets.lead-timeline';

    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    /** @var int The lead ID passed from the EditLead page */
    public int $leadId;

    public array $events = [];

    public int $perPage = 20;

    public function mount(int $leadId): void
    {
        $this->leadId = $leadId;
        $this->loadEvents();
    }

    public function loadEvents(): void
    {
        $lead = Lead::find($this->leadId);
        if (!$lead) {
            $this->events = [];
            return;
        }

        $events = collect();

        // 1. Activities (stage changes, notes, auto-tasks)
        Activity::where('related_type', 'Lead')
            ->where('related_id', $lead->id)
            ->with('user')
            ->latest()
            ->limit(50)
            ->get()
            ->each(function (Activity $a) use (&$events) {
                $events->push([
                    'type'    => 'activity',
                    'subtype' => $a->type,
                    'icon'    => $this->activityIcon($a->type),
                    'color'   => $this->activityColor($a->type),
                    'title'   => $this->activityTitle($a->type),
                    'body'    => $a->description,
                    'user'    => $a->user?->name ?? 'System',
                    'at'      => Carbon::parse($a->created_at),
                    'at_human'=> Carbon::parse($a->created_at)->diffForHumans(),
                    'at_fmt'  => Carbon::parse($a->created_at)->format('M j, Y g:i A'),
                ]);
            });

        // 2. Call logs
        CallLog::where('lead_id', $lead->id)
            ->with('user')
            ->latest('called_at')
            ->limit(50)
            ->get()
            ->each(function (CallLog $c) use (&$events) {
                $events->push([
                    'type'    => 'call',
                    'subtype' => $c->outcome ?? 'call',
                    'icon'    => 'heroicon-o-phone',
                    'color'   => 'blue',
                    'title'   => 'Call — ' . ucwords(str_replace('_', ' ', $c->outcome ?? 'logged')),
                    'body'    => collect([
                        $c->duration_minutes ? $c->duration_minutes . ' min' : null,
                        $c->notes,
                    ])->filter()->implode(' · '),
                    'user'    => $c->user?->name ?? '—',
                    'at'      => Carbon::parse($c->called_at ?? $c->created_at),
                    'at_human'=> Carbon::parse($c->called_at ?? $c->created_at)->diffForHumans(),
                    'at_fmt'  => Carbon::parse($c->called_at ?? $c->created_at)->format('M j, Y g:i A'),
                ]);
            });

        // 3. SMS logs
        SmsLog::where('lead_id', $lead->id)
            ->with('user')
            ->latest('sent_at')
            ->limit(50)
            ->get()
            ->each(function (SmsLog $s) use (&$events) {
                $events->push([
                    'type'    => 'sms',
                    'subtype' => $s->direction,
                    'icon'    => 'heroicon-o-chat-bubble-left-ellipsis',
                    'color'   => $s->direction === 'inbound' ? 'emerald' : 'violet',
                    'title'   => 'SMS ' . ucfirst($s->direction),
                    'body'    => $s->message,
                    'user'    => $s->user?->name ?? ($s->direction === 'inbound' ? 'Lead' : '—'),
                    'at'      => Carbon::parse($s->sent_at ?? $s->created_at),
                    'at_human'=> Carbon::parse($s->sent_at ?? $s->created_at)->diffForHumans(),
                    'at_fmt'  => Carbon::parse($s->sent_at ?? $s->created_at)->format('M j, Y g:i A'),
                ]);
            });

        // 4. Email logs
        EmailLog::where('lead_id', $lead->id)
            ->with('user')
            ->latest('sent_at')
            ->limit(50)
            ->get()
            ->each(function (EmailLog $e) use (&$events) {
                $events->push([
                    'type'    => 'email',
                    'subtype' => $e->direction,
                    'icon'    => 'heroicon-o-envelope',
                    'color'   => $e->direction === 'inbound' ? 'sky' : 'amber',
                    'title'   => 'Email ' . ucfirst($e->direction) . ($e->subject ? ': ' . $e->subject : ''),
                    'body'    => $e->body_preview,
                    'user'    => $e->user?->name ?? ($e->direction === 'inbound' ? 'Lead' : '—'),
                    'at'      => Carbon::parse($e->sent_at ?? $e->created_at),
                    'at_human'=> Carbon::parse($e->sent_at ?? $e->created_at)->diffForHumans(),
                    'at_fmt'  => Carbon::parse($e->sent_at ?? $e->created_at)->format('M j, Y g:i A'),
                ]);
            });

        // 5. Tasks
        Task::where('related_type', 'Lead')
            ->where('related_id', $lead->id)
            ->with('assignedTo')
            ->latest()
            ->limit(50)
            ->get()
            ->each(function (Task $t) use (&$events) {
                $events->push([
                    'type'    => 'task',
                    'subtype' => $t->status,
                    'icon'    => $t->status === 'done' ? 'heroicon-o-check-circle' : 'heroicon-o-clipboard-document',
                    'color'   => match ($t->status) {
                        'done'        => 'green',
                        'in_progress' => 'yellow',
                        default       => 'gray',
                    },
                    'title'   => 'Task: ' . $t->title,
                    'body'    => collect([
                        $t->description,
                        $t->due_date ? 'Due ' . Carbon::parse($t->due_date)->format('M j, Y') : null,
                        'Priority: ' . ucfirst($t->priority ?? 'medium'),
                    ])->filter()->implode(' · '),
                    'user'    => $t->assignedTo?->name ?? '—',
                    'at'      => Carbon::parse($t->created_at),
                    'at_human'=> Carbon::parse($t->created_at)->diffForHumans(),
                    'at_fmt'  => Carbon::parse($t->created_at)->format('M j, Y g:i A'),
                ]);
            });

        $this->events = $events
            ->sortByDesc('at')
            ->take($this->perPage)
            ->values()
            ->toArray();
    }

    public function loadMore(): void
    {
        $this->perPage += 20;
        $this->loadEvents();
    }

    // ── helpers ─────────────────────────────────────────────────────────────

    private function activityIcon(string $type): string
    {
        return match ($type) {
            'status_change'  => 'heroicon-o-arrow-right-circle',
            'note'           => 'heroicon-o-pencil-square',
            'appointment'    => 'heroicon-o-calendar',
            'auto_task'      => 'heroicon-o-bolt',
            default          => 'heroicon-o-bell',
        };
    }

    private function activityColor(string $type): string
    {
        return match ($type) {
            'status_change' => 'orange',
            'note'          => 'indigo',
            'appointment'   => 'teal',
            default         => 'gray',
        };
    }

    private function activityTitle(string $type): string
    {
        return match ($type) {
            'status_change' => 'Stage Changed',
            'note'          => 'Note Added',
            'appointment'   => 'Appointment',
            'auto_task'     => 'Auto-Task Created',
            default         => ucwords(str_replace('_', ' ', $type)),
        };
    }
}
