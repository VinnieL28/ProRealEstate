<?php

namespace App\Filament\Resources\LeadResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;

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
                Tables\Columns\TextColumn::make('sent_at')->dateTime(),
                Tables\Columns\TextColumn::make('user.name')->label('By'),
                Tables\Columns\TextColumn::make('direction')->badge(),
                Tables\Columns\TextColumn::make('message')->limit(40),
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
