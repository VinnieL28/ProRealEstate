<?php

namespace App\Observers;

use App\Models\ColdLead;
use App\Services\Kpi\KpiMetricRecorder;
use Illuminate\Support\Arr;

class ColdLeadObserver
{
    public function __construct(private readonly KpiMetricRecorder $recorder)
    {
    }

    public function created(ColdLead $lead): void
    {
        $this->recorder->record('leads.cold.created');
        $this->logContactActivity($lead, 'Cold lead created');
    }

    public function updated(ColdLead $lead): void
    {
        if (!$lead->wasChanged()) {
            return;
        }

        $changes = Arr::only($lead->getChanges(), [
            'status',
            'drip_status',
            'drip_step',
            'team_assigned',
        ]);

        $this->logContactActivity($lead, 'Cold lead updated', $changes);

        if ($lead->wasChanged('status')) {
            $this->recorder->record("leads.cold.status.{$lead->status}");
        }

        if ($lead->wasChanged('drip_status') && $lead->drip_status === 'paused') {
            $this->recorder->record('leads.cold.drip.paused');
        }
    }

    protected function logContactActivity(ColdLead $lead, string $message, array $properties = []): void
    {
        if (!$lead->contact) {
            return;
        }

        activity()
            ->performedOn($lead->contact)
            ->event('cold_lead.update')
            ->withProperties($properties)
            ->log($message);
    }
}
