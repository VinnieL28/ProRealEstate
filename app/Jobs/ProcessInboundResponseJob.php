<?php

namespace App\Jobs;

use App\Services\Drip\HandleInboundResponseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessInboundResponseJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected string $class,
        protected int $id,
        protected string $message,
        protected string $channel = 'manual'
    ) {
    }

    public function handle(HandleInboundResponseService $service): void
    {
        $model = $this->class::query()->find($this->id);

        if (!$model) {
            return;
        }

        $service->pauseAndAssign($model, $this->message, $this->channel);
    }
}
