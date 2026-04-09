<?php

namespace App\Filament\Resources\LeadResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;

class PropertiesRelationManager extends RelationManager
{
    protected static string $relationship = 'properties';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('address')->required(),
                TextInput::make('city'),
                TextInput::make('state'),
                TextInput::make('zip'),
                Select::make('type')->options([
                    'sfr' => 'SFR',
                    'multi_family' => 'Multi-family',
                    'land' => 'Land',
                    'condo' => 'Condo',
                    'other' => 'Other',
                ])->default('sfr'),
                Select::make('status')->options([
                    'prospect' => 'Prospect',
                    'under_contract' => 'Under Contract',
                    'owned' => 'Owned',
                    'listed' => 'Listed',
                    'sold' => 'Sold',
                    'dead' => 'Dead',
                ])->default('prospect'),
                TextInput::make('beds')->numeric(),
                TextInput::make('baths')->numeric(),
                TextInput::make('sqft')->numeric(),
                TextInput::make('arv')->numeric(),
                TextInput::make('estimated_repairs')->numeric(),
                TextInput::make('acquisition_price')->numeric(),
                TextInput::make('sale_price')->numeric(),
                Textarea::make('notes')->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('address')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('city'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('arv')->money('usd', true),
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
