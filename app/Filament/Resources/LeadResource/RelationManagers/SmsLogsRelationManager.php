<?php

namespace App\Filament\Resources\LeadResource\RelationManagers;

use App\Models\SmsLog;
use App\Models\User;
use App\Services\TwilioService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class SmsLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'smsLogs';

    public function form(Form $form): Form
    {
        return $form->schema([
            DateTimePicker::make('sent_at')->required(),
            Select::make('direction')->options([
                'inbound' => 'Inbound',
                'outbound' => 'Outbound',
            ])->default('outbound'),
            Select::make('user_id')->label('Logged By')->options(fn () => User::orderBy('name')->whereNotNull('name')->pluck('name', 'id')->toArray())->searchable(),
            Textarea::make('message')->rows(3)->required(),
            Textarea::make('notes')->rows(3),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sent_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('By'),
                Tables\Columns\BadgeColumn::make('direction')
                    ->colors(['success' => 'outbound', 'info' => 'inbound']),
                Tables\Columns\TextColumn::make('message')->limit(60)->wrap(),
            ])
            ->defaultSort('sent_at', 'desc')
            ->headerActions([
                // Send real SMS via Twilio
                Action::make('send_sms')
                    ->label('Send SMS')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->form([
                        Textarea::make('message')
                            ->label('Message')
                            ->required()
                            ->rows(3)
                            ->maxLength(1600),
                    ])
                    ->action(function (array $data) {
                        $lead = $this->ownerRecord;
                        $phone = $lead->primary_phone ?? $lead->phone;

                        if (!$phone) {
                            Notification::make()->title('No phone number on this lead')->danger()->send();
                            return;
                        }

                        $twilio = new TwilioService();
                        $sent = $twilio->sendSms($phone, $data['message'], $lead->team_id);

                        if ($sent) {
                            SmsLog::create([
                                'team_id'   => $lead->team_id,
                                'lead_id'   => $lead->id,
                                'user_id'   => auth()->id(),
                                'sent_at'   => now(),
                                'direction' => 'outbound',
                                'message'   => $data['message'],
                            ]);

                            Notification::make()->title('SMS sent successfully')->success()->send();
                        } else {
                            Notification::make()
                                ->title('SMS failed — check Twilio credentials in Settings')
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\CreateAction::make()
                    ->label('Log SMS Manually')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['team_id'] = $this->ownerRecord->team_id;
                        $data['lead_id'] = $this->ownerRecord->id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->mutateFormDataUsing(function (array $data): array {
                    $data['team_id'] = $this->ownerRecord->team_id;
                    $data['lead_id'] = $this->ownerRecord->id;
                    return $data;
                }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
