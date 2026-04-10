<?php

namespace App\Events;

use App\Models\Lead;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadStageChangedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Lead $lead,
        public string $oldStage,
        public string $newStage,
    ) {}
}
