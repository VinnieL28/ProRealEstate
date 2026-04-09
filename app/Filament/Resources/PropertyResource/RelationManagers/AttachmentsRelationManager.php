<?php

namespace App\Filament\Resources\PropertyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required(),
            FileUpload::make('path')
                ->disk(config('filesystems.default'))
                ->directory('attachments')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('mime_type'),
                Tables\Columns\TextColumn::make('created_at')->since(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data) {
                    $data['team_id'] = $this->ownerRecord->team_id;
                    $data['related_type'] = 'Property';
                    $data['related_id'] = $this->ownerRecord->id;
                    $data['mime_type'] = Storage::mimeType($data['path']) ?: null;
                    $data['size'] = Storage::size($data['path']) ?: null;
                    $data['uploaded_by'] = auth()->id();
                    return $data;
                }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
