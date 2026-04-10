<?php

namespace App\Filament\Resources\LeadResource\RelationManagers;

use App\Mail\LeadEmail;
use App\Models\EmailLog;
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

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('direction')
                    ->colors(['success' => 'outbound', 'info' => 'inbound']),
                Tables\Columns\TextColumn::make('subject')->limit(60)->searchable(),
                Tables\Columns\TextColumn::make('from_address')->label('From')->limit(40),
                Tables\Columns\TextColumn::make('to_address')->label('To')->limit(40),
                Tables\Columns\TextColumn::make('sent_at')->dateTime()->sortable(),
            ])
            ->defaultSort('sent_at', 'desc')
            ->headerActions([
                Action::make('send_email')
                    ->label('Send Email')
                    ->icon('heroicon-o-envelope')
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
                        $lead = $this->getOwnerRecord();
                        $user = auth()->user();

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

                        EmailLog::create([
                            'team_id'      => $user?->team_id,
                            'user_id'      => $user?->id,
                            'lead_id'      => $lead->id,
                            'direction'    => 'outbound',
                            'subject'      => $data['subject'],
                            'from_address' => config('mail.from.address'),
                            'to_address'   => $data['to'],
                            'body_preview' => substr($data['body'], 0, 500),
                            'sent_at'      => now(),
                        ]);

                        Notification::make()->title('Email sent successfully.')->success()->send();
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }
}
