<?php

namespace App\Jobs;

use App\Services\GmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncGmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(public int $teamId) {}

    public function handle(GmailService $gmail): void
    {
        $count = $gmail->syncInbox($this->teamId);
        Log::info("SyncGmailJob: imported {$count} email(s) for team {$this->teamId}");
    }
}
