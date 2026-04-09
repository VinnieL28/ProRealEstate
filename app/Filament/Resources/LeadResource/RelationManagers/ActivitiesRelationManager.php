<?php

namespace App\Filament\Resources\LeadResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('type')->required(),
            Textarea::make('description')->rows(3),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('description')->limit(50),
                Tables\Columns\TextColumn::make('created_at')->since(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data) {
                    $data['team_id'] = $this->ownerRecord->team_id;
                    $data['related_type'] = 'Lead';
                    $data['related_id'] = $this->ownerRecord->id;
                    $data['user_id'] = auth()->id();
                    return $data;
                }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
