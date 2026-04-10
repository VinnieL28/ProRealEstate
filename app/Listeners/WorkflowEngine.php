<?php

namespace App\Listeners;

use App\Events\DealClosedEvent;
use App\Events\LeadCreatedEvent;
use App\Events\LeadStageChangedEvent;
use App\Models\Lead;
use App\Models\Task;
use App\Models\WorkflowRule;
use App\Services\TwilioService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class WorkflowEngine implements ShouldQueue
{
    public function handleLeadCreated(LeadCreatedEvent $event): void
    {
        $this->runRules('lead_created', $event->lead->team_id, ['lead' => $event->lead]);
    }

    public function handleLeadStageChanged(LeadStageChangedEvent $event): void
    {
        $this->runRules('stage_changed', $event->lead->team_id, [
            'lead'      => $event->lead,
            'old_stage' => $event->oldStage,
            'new_stage' => $event->newStage,
        ]);
    }

    public function handleDealClosed(DealClosedEvent $event): void
    {
        $this->runRules('deal_closed', $event->deal->team_id, ['deal' => $event->deal]);
    }

    private function runRules(string $trigger, ?int $teamId, array $context): void
    {
        $rules = WorkflowRule::where('trigger', $trigger)
            ->where('is_active', true)
            ->where(fn ($q) => $q->where('team_id', $teamId)->orWhereNull('team_id'))
            ->get();

        foreach ($rules as $rule) {
            if ($this->conditionsMet($rule->conditions ?? [], $context)) {
                $this->executeActions($rule->actions ?? [], $context);
            }
        }
    }

    private function conditionsMet(array $conditions, array $context): bool
    {
        if (empty($conditions)) {
            return true;
        }

        $lead = $context['lead'] ?? null;
        $deal = $context['deal'] ?? null;

        foreach ($conditions as $condition) {
            $field    = $condition['field'] ?? '';
            $operator = $condition['operator'] ?? '=';
            $value    = $condition['value'] ?? '';

            // Resolve field value from context
            $actual = null;
            if ($lead && isset($lead->$field)) {
                $actual = $lead->$field;
            } elseif ($deal && isset($deal->$field)) {
                $actual = $deal->$field;
            } elseif ($field === 'new_stage') {
                $actual = $context['new_stage'] ?? null;
            }

            $match = match ($operator) {
                '='  => $actual == $value,
                '!=' => $actual != $value,
                '>'  => $actual >  $value,
                '>=' => $actual >= $value,
                '<'  => $actual <  $value,
                '<=' => $actual <= $value,
                default => true,
            };

            if (!$match) {
                return false;
            }
        }

        return true;
    }

    private function executeActions(array $actions, array $context): void
    {
        $lead = $context['lead'] ?? null;
        $deal = $context['deal'] ?? null;

        foreach ($actions as $action) {
            $type = $action['type'] ?? '';

            try {
                match ($type) {
                    'send_email'  => $this->doSendEmail($action, $lead, $deal),
                    'send_sms'    => $this->doSendSms($action, $lead),
                    'create_task' => $this->doCreateTask($action, $lead, $deal),
                    'assign_agent'=> $this->doAssignAgent($action, $lead),
                    default       => null,
                };
            } catch (\Throwable $e) {
                Log::error("WorkflowEngine action [{$type}] failed: " . $e->getMessage());
            }
        }
    }

    private function doSendEmail(array $action, ?Lead $lead, mixed $deal): void
    {
        if (!$lead) return;
        $email = $lead->primary_email ?? $lead->email;
        if (!$email) return;

        Mail::raw($action['body'] ?? 'Automated message from Pro Real Estate CRM', function ($msg) use ($action, $email) {
            $msg->to($email)->subject($action['subject'] ?? 'Message from Pro Real Estate');
        });
    }

    private function doSendSms(array $action, ?Lead $lead): void
    {
        if (!$lead) return;
        $phone = $lead->primary_phone ?? $lead->phone;
        if (!$phone) return;

        app(TwilioService::class)->sendSms($phone, $action['message'] ?? 'Hello from Pro Real Estate CRM', $lead->team_id);
    }

    private function doCreateTask(array $action, ?Lead $lead, mixed $deal): void
    {
        Task::create([
            'team_id'      => $lead?->team_id ?? $deal?->team_id,
            'title'        => $action['title'] ?? 'Workflow Task',
            'description'  => $action['description'] ?? 'Auto-created by workflow rule.',
            'due_date'     => now()->addDays((int)($action['due_in_days'] ?? 1)),
            'related_type' => $lead ? 'Lead' : 'Deal',
            'related_id'   => $lead?->id ?? $deal?->id,
            'status'       => 'open',
            'priority'     => $action['priority'] ?? 'medium',
            'assigned_to_id' => $lead?->assigned_to_id,
        ]);
    }

    private function doAssignAgent(array $action, ?Lead $lead): void
    {
        if (!$lead || !isset($action['user_id'])) return;
        $lead->update(['assigned_to_id' => $action['user_id']]);
    }
}
