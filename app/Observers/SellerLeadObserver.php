<?php

namespace App\Observers;

use App\Models\SellerLead;
use App\Services\Kpi\KpiMetricRecorder;
use Illuminate\Support\Arr;

class SellerLeadObserver
{
    public function __construct(private readonly KpiMetricRecorder $recorder)
    {
    }

    public function created(SellerLead $lead): void
    {
        $this->recorder->record('leads.seller.created');
        $this->logContactActivity($lead, 'Seller lead created');
    }

    public function updated(SellerLead $lead): void
    {
        if (!$lead->wasChanged()) {
            return;
        }

        $changes = Arr::only($lead->getChanges(), [
            'status',
            'drip_status',
            'drip_step',
            'lead_source',
        ]);

        $this->logContactActivity($lead, 'Seller lead updated', $changes);

        if ($lead->wasChanged('status')) {
            $this->recorder->record("leads.seller.status.{$lead->status}");
        }
    }

    protected function logContactActivity(SellerLead $lead, string $message, array $properties = []): void
    {
        if (!$lead->contact) {
            return;
        }

        activity()
            ->performedOn($lead->contact)
            ->event('seller_lead.update')
            ->withProperties($properties)
            ->log($message);
    }
}
