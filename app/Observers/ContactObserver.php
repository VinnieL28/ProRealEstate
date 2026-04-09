<?php

namespace App\Observers;

use App\Models\Contact;
use App\Services\Kpi\KpiMetricRecorder;

class ContactObserver
{
    public function __construct(private readonly KpiMetricRecorder $recorder)
    {
    }

    public function created(Contact $contact): void
    {
        $this->recorder->record('contacts.created');
    }

    public function updated(Contact $contact): void
    {
        if ($contact->wasChanged()) {
            $this->recorder->record('contacts.updated');
        }
    }
}
