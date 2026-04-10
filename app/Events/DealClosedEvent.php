<?php

namespace App\Events;

use App\Models\Deal;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DealClosedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(public Deal $deal) {}
}
