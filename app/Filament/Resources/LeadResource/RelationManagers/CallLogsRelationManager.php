<?php

namespace App\Filament\Resources\LeadResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;

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
            Select::make('user_id')->label('Logged By')->options(fn () => User::orderBy('name')->pluck('name', 'id'))->searchable(),
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
                Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data): array {
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
