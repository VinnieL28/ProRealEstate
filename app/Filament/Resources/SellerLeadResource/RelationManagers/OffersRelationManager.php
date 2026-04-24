<?php

namespace App\Filament\Resources\SellerLeadResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OffersRelationManager extends RelationManager
{
    protected static string $relationship = 'offers';
    protected static ?string $title = 'Offers';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('buyer_name')->required(),
            TextInput::make('amount')->numeric()->prefix('$')->required(),
            Select::make('status')->options([
                'pending'  => 'Pending',
                'accepted' => 'Accepted',
                'rejected' => 'Rejected',
                'countered'=> 'Countered',
            ])->default('pending'),
            Textarea::make('notes')->rows(2)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('buyer_name')->label('Buyer')->searchable()->weight('bold'),
                TextColumn::make('amount')->money('usd')->sortable(),
                TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'accepted' => 'success',
                    'rejected' => 'danger',
                    'countered'=> 'warning',
                    default    => 'gray',
                }),
                TextColumn::make('notes')->limit(40),
                TextColumn::make('created_at')->label('Date')->date()->sortable(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
