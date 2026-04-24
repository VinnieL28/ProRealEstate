<?php

namespace App\Filament\Resources\DripCampaignResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StepsRelationManager extends RelationManager
{
    protected static string $relationship = 'steps';
    protected static ?string $title = 'Steps';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('step_order')->label('Order')->numeric()->default(1)->required(),
            Select::make('channel')->options(['email' => 'Email', 'sms' => 'SMS'])->default('email')->required(),
            TextInput::make('delay_days')->label('Send after (days)')->numeric()->default(0)->required(),
            TextInput::make('subject')->label('Subject (email only)')->columnSpanFull(),
            Textarea::make('body')->label('Message / Body')->rows(4)->required()->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('step_order')
            ->reorderable('step_order')
            ->columns([
                TextColumn::make('step_order')->label('#')->sortable(),
                TextColumn::make('channel')->badge()->color(fn ($state) => $state === 'sms' ? 'success' : 'info'),
                TextColumn::make('delay_days')->label('Delay (days)'),
                TextColumn::make('subject')->limit(30)->placeholder('—'),
                TextColumn::make('body')->limit(50),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
