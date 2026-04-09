<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsLogResource\Pages;
use App\Models\Lead;
use App\Models\SmsLog;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SmsLogResource extends Resource
{
    protected static ?string $model = SmsLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationGroup = 'Communications';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('lead_id')->options(fn () => Lead::orderBy('owner_name')->pluck('owner_name', 'id'))->searchable()->required(),
            Select::make('user_id')->label('Logged By')->options(fn () => User::orderBy('name')->pluck('name', 'id'))->searchable(),
            DateTimePicker::make('sent_at')->required(),
            Select::make('direction')->options([
                'inbound' => 'Inbound',
                'outbound' => 'Outbound',
            ])->default('outbound'),
            Textarea::make('message')->rows(3)->required(),
            Textarea::make('notes')->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lead.owner_name')->label('Lead')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('By'),
                Tables\Columns\TextColumn::make('sent_at')->dateTime(),
                Tables\Columns\BadgeColumn::make('direction'),
                Tables\Columns\TextColumn::make('message')->limit(40),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsLogs::route('/'),
            'create' => Pages\CreateSmsLog::route('/create'),
            'edit' => Pages\EditSmsLog::route('/{record}/edit'),
        ];
    }
}
