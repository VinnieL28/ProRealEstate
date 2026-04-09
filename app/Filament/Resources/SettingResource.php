<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Pipeline & Defaults')->schema([
                    TextInput::make('default_currency')->label('Default Currency')->default('USD'),
                    Repeater::make('pipeline_stages')
                        ->schema([
                            TextInput::make('key')->required(),
                            TextInput::make('label')->required(),
                        ])
                        ->label('Pipeline Stages')
                        ->default([
                            ['key' => 'new_lead', 'label' => 'New Lead'],
                            ['key' => 'no_contact', 'label' => 'No Contact Made'],
                            ['key' => 'contact_made', 'label' => 'Contact Made'],
                            ['key' => 'appointment_set', 'label' => 'Appointments Set'],
                            ['key' => 'due_diligence', 'label' => 'Due Diligence'],
                            ['key' => 'offer_made', 'label' => 'Offers Made'],
                            ['key' => 'under_contract', 'label' => 'Under Contract'],
                            ['key' => 'closed_won', 'label' => 'Closed Won'],
                            ['key' => 'closed_lost', 'label' => 'Closed Lost'],
                        ])
                        ->columns(2),
                    TagsInput::make('lead_sources')->placeholder('Add source')->helperText('e.g. Cold Call, SMS, PPC'),
                    TagsInput::make('team_phone_numbers')->placeholder('Add phone number'),
                ]),
                Section::make('Telephony & Inbox')->columns(2)->schema([
                    TextInput::make('twilio_sid'),
                    TextInput::make('twilio_token')->password(),
                    TextInput::make('twilio_phone_number')->label('Twilio Phone'),
                    TextInput::make('inbox_provider')->placeholder('Twilio / Pumble / Other'),
                ]),
                Section::make('Email / Gmail')->columns(2)->schema([
                    TextInput::make('email_provider')->placeholder('Gmail / Other'),
                    TextInput::make('gmail_client_id'),
                    TextInput::make('gmail_client_secret')->password(),
                    TextInput::make('gmail_refresh_token')->password(),
                ]),
                Section::make('E-Signature & Files')->columns(2)->schema([
                    TextInput::make('docusign_client_id'),
                    TextInput::make('docusign_secret')->password(),
                    TextInput::make('docusign_account_id'),
                    TextInput::make('file_storage_driver')->placeholder('s3/local/custom'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('team.name')->label('Team'),
                Tables\Columns\TextColumn::make('default_currency'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
