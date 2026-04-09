<?php

namespace App\Filament\Pages;

use App\Jobs\SyncGmailJob;
use App\Models\EmailLog;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class GmailPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Communications';
    protected static string $view = 'filament.pages.gmail';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['owner', 'admin', 'acquisition_manager', 'lead_manager', 'dispo_manager'], true);
    }

    public array $emails = [];
    public bool $isConnected = false;

    public function mount(): void
    {
        $teamId = auth()->user()?->team_id;
        $setting = Setting::where('team_id', $teamId)->first();
        $this->isConnected = !empty($setting?->gmail_refresh_token);

        $this->emails = EmailLog::query()
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->latest('sent_at')
            ->limit(50)
            ->get()
            ->toArray();
    }

    public function syncEmails(): void
    {
        $teamId = auth()->user()?->team_id;
        SyncGmailJob::dispatch($teamId);
        Notification::make()->title('Sync queued — emails will appear shortly.')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('connect_gmail')
                ->label('Connect Gmail')
                ->icon('heroicon-o-link')
                ->color('warning')
                ->url(route('gmail.connect'))
                ->visible(fn () => !$this->isConnected),

            Action::make('sync')
                ->label('Sync Inbox')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action('syncEmails')
                ->visible(fn () => $this->isConnected),

            Action::make('compose')
                ->label('Compose')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->visible(fn () => $this->isConnected)
                ->form([
                    TextInput::make('to')->label('To')->email()->required(),
                    TextInput::make('subject')->label('Subject')->required(),
                    RichEditor::make('body')
                        ->label('Message')
                        ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'link'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $teamId = auth()->user()?->team_id;
                    $sent   = app(\App\Services\GmailService::class)->sendEmail(
                        $teamId,
                        $data['to'],
                        $data['subject'],
                        strip_tags($data['body'])
                    );

                    if ($sent) {
                        Notification::make()->title('Email sent.')->success()->send();
                    } else {
                        Notification::make()->title('Failed to send — check Gmail credentials.')->danger()->send();
                    }
                }),
        ];
    }
}
