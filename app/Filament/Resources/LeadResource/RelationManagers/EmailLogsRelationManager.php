<?php

namespace App\Filament\Resources\LeadResource\RelationManagers;

use App\Mail\LeadEmail;
use App\Models\EmailLog;
use App\Models\Setting;
use App\Services\GmailService;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class EmailLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'emailLogs';

    protected static ?string $title = 'Email History';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('subject')->required(),
            Textarea::make('body_preview')->label('Body')->rows(4),
        ]);
    }

    private function isGmailConnected(): bool
    {
        $teamId  = auth()->user()?->team_id;
        $setting = Setting::where('team_id', $teamId)->first();
        return !empty($setting?->gmail_refresh_token);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('direction')
                    ->colors(['success' => 'outbound', 'info' => 'inbound']),
                Tables\Columns\TextColumn::make('subject')->limit(60)->searchable(),
                Tables\Columns\TextColumn::make('from_address')->label('From')->limit(40),
                Tables\Columns\TextColumn::make('to_address')->label('To')->limit(40),
                Tables\Columns\TextColumn::make('body_preview')->label('Preview')->limit(60)->wrap(),
                Tables\Columns\TextColumn::make('sent_at')->dateTime()->sortable(),
            ])
            ->defaultSort('sent_at', 'desc')
            ->headerActions([
                // Send via Gmail if connected, otherwise fall back to Laravel Mail
                Action::make('send_email')
                    ->label(fn () => $this->isGmailConnected() ? 'Send via Gmail' : 'Send Email')
                    ->icon('heroicon-o-envelope')
                    ->color('success')
                    ->form([
                        TextInput::make('to')
                            ->label('To')
                            ->email()
                            ->default(fn () => $this->getOwnerRecord()->primary_email ?? $this->getOwnerRecord()->email)
                            ->required(),
                        TextInput::make('subject')->required(),
                        Textarea::make('body')->rows(6)->required(),
                    ])
                    ->action(function (array $data) {
                        $lead    = $this->getOwnerRecord();
                        $user    = auth()->user();
                        $teamId  = $user?->team_id;

                        if ($this->isGmailConnected()) {
                            // Send via Gmail API
                            $gmail = new GmailService();
                            $sent  = $gmail->sendEmail($teamId, $data['to'], $data['subject'], $data['body']);

                            if (!$sent) {
                                Notification::make()
                                    ->title('Gmail send failed — check credentials in Settings')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $fromAddress = Setting::where('team_id', $teamId)->value('smtp_from_address')
                                ?? config('mail.from.address');
                        } else {
                            // Fall back to Laravel Mail
                            try {
                                Mail::to($data['to'])->send(new LeadEmail(
                                    lead: $lead,
                                    subject: $data['subject'],
                                    body: $data['body'],
                                ));
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Email failed: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $fromAddress = config('mail.from.address');
                        }

                        EmailLog::create([
                            'team_id'      => $teamId,
                            'user_id'      => $user?->id,
                            'lead_id'      => $lead->id,
                            'direction'    => 'outbound',
                            'subject'      => $data['subject'],
                            'from_address' => $fromAddress,
                            'to_address'   => $data['to'],
                            'body_preview' => substr($data['body'], 0, 500),
                            'sent_at'      => now(),
                        ]);

                        Notification::make()->title('Email sent successfully.')->success()->send();
                    }),

                // Pull matching emails from Gmail inbox for this lead
                Action::make('sync_lead_emails')
                    ->label('Fetch from Gmail')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->visible(fn () => $this->isGmailConnected())
                    ->action(function () {
                        $lead   = $this->getOwnerRecord();
                        $email  = $lead->primary_email ?? $lead->email;

                        if (!$email) {
                            Notification::make()->title('Lead has no email address')->warning()->send();
                            return;
                        }

                        $gmail   = new GmailService();
                        $count   = $gmail->syncLeadEmails($lead->team_id, $lead->id, $email);

                        Notification::make()
                            ->title($count > 0 ? "Fetched {$count} new email(s) from Gmail" : 'No new emails found')
                            ->success()
                            ->send();
                    }),

                // Connect Gmail link (shown when not connected)
                Action::make('connect_gmail')
                    ->label('Connect Gmail')
                    ->icon('heroicon-o-link')
                    ->color('warning')
                    ->url(route('gmail.connect'))
                    ->visible(fn () => !$this->isGmailConnected()),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }
}
