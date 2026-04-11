<?php

namespace App\Filament\Pages;

use App\Models\Activity;
use App\Models\CallLog;
use App\Models\Lead;
use App\Models\SmsLog;
use App\Models\Task;
use App\Services\TwilioService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class LeadKanban extends Page
{
    protected static ?string $navigationLabel = 'Lead Kanban';
    protected static ?string $navigationGroup = 'Leads';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $slug = 'lead-kanban';
    protected static string $view = 'filament.pages.lead-kanban';
    protected static ?int $navigationSort = 20;

    /** @var array<string, array{label:string, leads:\Illuminate\Support\Collection}> */
    public array $columns = [];

    public function mount(): void
    {
        $this->loadColumns();
    }

    protected function loadColumns(): void
    {
        $stages = [
            'new_lead'        => 'New',
            'no_contact'      => 'No Contact',
            'contact_made'    => 'Contacted',
            'appointment_set' => 'Appointment',
            'due_diligence'   => 'Due Diligence',
            'offer_made'      => 'Offer',
            'under_contract'  => 'Contract',
            'closed_won'      => 'Closed Won',
            'closed_lost'     => 'Closed Lost',
        ];

        $user   = auth()->user();
        $teamId = $user?->team_id;

        $query = Lead::query()
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->orderByDesc('updated_at')
            ->get();

        $columns = [];
        foreach ($stages as $key => $label) {
            $columns[$key] = [
                'label' => $label,
                'leads' => $query->where('stage', $key)->values(),
            ];
        }

        $this->columns = $columns;
    }

    /**
     * Called by SortableJS via Alpine/Livewire when a card is dragged to a new column.
     */
    public function moveCard(int $leadId, string $newStage): void
    {
        $user = auth()->user();
        $lead = Lead::where('id', $leadId)
            ->when($user?->team_id, fn ($q) => $q->where('team_id', $user->team_id))
            ->first();

        if (!$lead) {
            return;
        }

        $lead->update(['stage' => $newStage]);

        $this->loadColumns();

        Notification::make()
            ->title('Lead moved to ' . ucwords(str_replace('_', ' ', $newStage)))
            ->success()
            ->send();
    }

    /**
     * Quick action: Log a call for a lead.
     */
    public function logCall(int $leadId): void
    {
        $user = auth()->user();
        $lead = Lead::find($leadId);
        if (!$lead) return;

        CallLog::create([
            'team_id'   => $lead->team_id,
            'lead_id'   => $lead->id,
            'user_id'   => $user?->id,
            'called_at' => now(),
            'outcome'   => 'no_answer',
            'notes'     => 'Quick log from Kanban',
        ]);

        Activity::create([
            'team_id'      => $lead->team_id,
            'user_id'      => $user?->id,
            'related_type' => 'Lead',
            'related_id'   => $lead->id,
            'type'         => 'call',
            'description'  => 'Call logged from Kanban board',
        ]);

        Notification::make()->title('Call logged for ' . ($lead->owner_name ?? 'Lead'))->success()->send();
    }

    /**
     * Quick action: Send a pre-set SMS to a lead.
     */
    public function sendQuickSms(int $leadId): void
    {
        $user  = auth()->user();
        $lead  = Lead::find($leadId);
        if (!$lead) return;

        $phone = $lead->primary_phone ?? $lead->phone;
        if (!$phone) {
            Notification::make()->title('No phone number for this lead')->warning()->send();
            return;
        }

        $message = 'Hi ' . ($lead->first_name ?? $lead->owner_name ?? 'there') . ', just checking in — are you still considering selling your property? Reply anytime!';

        $twilio = new TwilioService();
        $sent   = $twilio->sendSms($phone, $message, $lead->team_id);

        if ($sent) {
            SmsLog::create([
                'team_id'   => $lead->team_id,
                'lead_id'   => $lead->id,
                'user_id'   => $user?->id,
                'sent_at'   => now(),
                'direction' => 'outbound',
                'message'   => $message,
                'notes'     => 'Quick SMS from Kanban',
            ]);

            Notification::make()->title('SMS sent to ' . ($lead->owner_name ?? 'Lead'))->success()->send();
        } else {
            Notification::make()->title('SMS failed — check Twilio credentials')->danger()->send();
        }
    }

    /**
     * Quick action: Set an appointment (creates a Task due in 24h).
     */
    public function setAppointment(int $leadId): void
    {
        $user = auth()->user();
        $lead = Lead::find($leadId);
        if (!$lead) return;

        Task::create([
            'team_id'        => $lead->team_id,
            'title'          => 'Appointment with ' . ($lead->owner_name ?? 'Lead #' . $lead->id),
            'due_date'       => now()->addDay(),
            'related_type'   => 'Lead',
            'related_id'     => $lead->id,
            'status'         => 'open',
            'priority'       => 'high',
            'assigned_to_id' => $user?->id,
        ]);

        // Move lead to appointment_set stage
        $lead->update(['stage' => 'appointment_set']);
        $this->loadColumns();

        Notification::make()->title('Appointment task created & lead moved to Appointment stage')->success()->send();
    }
}
