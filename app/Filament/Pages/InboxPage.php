<?php

namespace App\Filament\Pages;

use App\Models\CallLog;
use App\Models\SmsLog;
use Filament\Pages\Page;

class InboxPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static string $view = 'filament.pages.inbox';

    protected static ?string $navigationGroup = 'Communications';

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public array $calls = [];
    public array $sms = [];

    public function mount(): void
    {
        $userId = auth()->id();
        $teamId = auth()->user()?->team_id;

        $this->calls = CallLog::query()
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->where(function ($q) use ($userId) {
                $q->whereNull('user_id')->orWhere('user_id', $userId);
            })
            ->latest('called_at')
            ->limit(50)
            ->get()
            ->toArray();

        $this->sms = SmsLog::query()
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->where(function ($q) use ($userId) {
                $q->whereNull('user_id')->orWhere('user_id', $userId);
            })
            ->latest('sent_at')
            ->limit(50)
            ->get()
            ->toArray();
    }
}
