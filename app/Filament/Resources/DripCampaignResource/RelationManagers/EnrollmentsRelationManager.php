<?php

namespace App\Filament\Resources\DripCampaignResource\RelationManagers;

use App\Models\Lead;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';
    protected static ?string $title = 'Enrolled Leads';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('lead_id')
                ->label('Lead')
                ->options(fn () => Lead::where('team_id', auth()->user()?->team_id)
                    ->orderBy('owner_name')
                    ->pluck('owner_name', 'id'))
                ->searchable()
                ->required(),
            Select::make('status')->options([
                'active'       => 'Active',
                'paused'       => 'Paused',
                'completed'    => 'Completed',
                'unsubscribed' => 'Unsubscribed',
            ])->default('active'),
            TextInput::make('current_step')->numeric()->default(0),
            DateTimePicker::make('next_send_at')->label('Next Send At'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lead.owner_name')->label('Lead')->searchable()->weight('bold'),
                TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'active'       => 'success',
                    'paused'       => 'warning',
                    'completed'    => 'gray',
                    'unsubscribed' => 'danger',
                    default        => 'gray',
                }),
                TextColumn::make('current_step')->label('Step'),
                TextColumn::make('next_send_at')->label('Next Send')->dateTime()->sortable(),
                TextColumn::make('enrolled_at')->label('Enrolled')->date()->sortable(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data) {
                $data['team_id']     = auth()->user()?->team_id;
                $data['enrolled_at'] = now();
                return $data;
            })])
            ->actions([
                Tables\Actions\Action::make('pause')
                    ->label('Pause')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->action(fn ($record) => $record->update(['status' => 'paused']))
                    ->visible(fn ($record) => $record->status === 'active'),
                Tables\Actions\Action::make('resume')
                    ->label('Resume')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->action(fn ($record) => $record->update(['status' => 'active']))
                    ->visible(fn ($record) => $record->status === 'paused'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
