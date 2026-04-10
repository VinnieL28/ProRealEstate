<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CallLogResource\Pages;
use App\Models\CallLog;
use App\Models\Lead;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CallLogResource extends Resource
{
    protected static ?string $model = CallLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone';

    protected static ?string $navigationGroup = 'Communications';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('lead_id')->options(fn () => Lead::orderBy('owner_name')->pluck('owner_name', 'id'))->searchable()->required(),
            Select::make('user_id')->label('Logged By')->options(fn () => User::orderBy('name')->pluck('name', 'id'))->searchable(),
            DateTimePicker::make('called_at')->required(),
            TextInput::make('duration_minutes')->numeric()->label('Duration (minutes)'),
            Select::make('outcome')->options([
                'no_answer' => 'No Answer',
                'left_vm' => 'Left VM',
                'spoke' => 'Spoke',
                'follow_up' => 'Follow-up Needed',
            ])->default('no_answer'),
            Textarea::make('notes')->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lead.owner_name')->label('Lead')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('By')->visibleFrom('sm'),
                Tables\Columns\TextColumn::make('called_at')->dateTime()->visibleFrom('md'),
                Tables\Columns\TextColumn::make('duration_minutes')->label('Minutes')->visibleFrom('md'),
                Tables\Columns\BadgeColumn::make('outcome'),
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
            'index' => Pages\ListCallLogs::route('/'),
            'create' => Pages\CreateCallLog::route('/create'),
            'edit' => Pages\EditCallLog::route('/{record}/edit'),
        ];
    }
}
