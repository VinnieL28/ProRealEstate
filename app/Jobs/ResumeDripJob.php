<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResumeDripJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected string $class,
        protected int $id
    ) {
    }

    public function handle(): void
    {
        $lead = $this->class::query()->find($this->id);

        if (!$lead) {
            return;
        }

        $lead->drip_status = 'active';
        $lead->drip_step = max(0, (int) $lead->drip_step);
        $lead->save();
    }
}
