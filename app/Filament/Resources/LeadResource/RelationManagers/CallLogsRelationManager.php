<?php

namespace App\Filament\Resources\LeadResource\RelationManagers;

use App\Models\CallLog;
use App\Models\User;
use App\Services\TwilioService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class CallLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'callLogs';

    public function form(Form $form): Form
    {
        return $form->schema([
            DateTimePicker::make('called_at')->required(),
            TextInput::make('duration_minutes')->numeric()->label('Duration (minutes)'),
            Select::make('outcome')->options([
                'no_answer' => 'No Answer',
                'left_vm' => 'Left VM',
                'spoke' => 'Spoke',
                'follow_up' => 'Follow-up Needed',
            ])->default('no_answer'),
            Select::make('user_id')->label('Logged By')->options(fn () => User::orderBy('name')->whereNotNull('name')->pluck('name', 'id')->toArray())->searchable(),
            Textarea::make('notes')->rows(3),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('called_at')->dateTime(),
                Tables\Columns\TextColumn::make('user.name')->label('By'),
                Tables\Columns\TextColumn::make('duration_minutes')->label('Minutes'),
                Tables\Columns\BadgeColumn::make('outcome'),
            ])
            ->headerActions([
                // Click-to-call via Twilio
                Action::make('click_to_call')
                    ->label('Click to Call')
                    ->icon('heroicon-o-phone')
                    ->color('success')
                    ->form([
                        Select::make('outcome')
                            ->label('Expected / Actual Outcome')
                            ->options([
                                'no_answer' => 'No Answer',
                                'left_vm'   => 'Left VM',
                                'spoke'     => 'Spoke',
                                'follow_up' => 'Follow-up Needed',
                            ])
                            ->default('no_answer'),
                        TextInput::make('duration_minutes')->numeric()->label('Duration (min)')->default(0),
                        Textarea::make('notes')->rows(2)->label('Call Notes'),
                    ])
                    ->action(function (array $data) {
                        $lead  = $this->ownerRecord;
                        $phone = $lead->primary_phone ?? $lead->phone;

                        if (!$phone) {
                            Notification::make()->title('No phone number on this lead')->danger()->send();
                            return;
                        }

                        $twilio   = new TwilioService();
                        $twimlUrl = url('/api/twilio/twiml/voice');
                        $initiated = $twilio->makeCall($phone, $twimlUrl, $lead->team_id);

                        CallLog::create([
                            'team_id'          => $lead->team_id,
                            'lead_id'          => $lead->id,
                            'user_id'          => auth()->id(),
                            'called_at'        => now(),
                            'duration_minutes' => $data['duration_minutes'] ?? 0,
                            'outcome'          => $data['outcome'],
                            'notes'            => $data['notes'] ?? null,
                        ]);

                        if ($initiated) {
                            Notification::make()->title('Call initiated via Twilio')->success()->send();
                        } else {
                            Notification::make()
                                ->title('Call logged (Twilio initiation failed — check credentials)')
                                ->warning()
                                ->send();
                        }
                    }),
                Tables\Actions\CreateAction::make()
                    ->label('Log Call Manually')
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
