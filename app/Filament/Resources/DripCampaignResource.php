<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DripCampaignResource\Pages;
use App\Filament\Resources\DripCampaignResource\RelationManagers\EnrollmentsRelationManager;
use App\Filament\Resources\DripCampaignResource\RelationManagers\StepsRelationManager;
use App\Models\DripCampaign;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DripCampaignResource extends Resource
{
    protected static ?string $model = DripCampaign::class;
    protected static ?string $navigationIcon  = 'heroicon-o-paper-airplane';
    protected static ?string $navigationGroup = 'Automation';
    protected static ?string $navigationLabel = 'Drip Campaigns';
    protected static ?int    $navigationSort  = 1;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['super_admin', 'owner', 'admin'], true);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $teamId = auth()->user()?->team_id;
        return $teamId ? $query->where('team_id', $teamId) : $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Campaign Details')->columns(2)->schema([
                TextInput::make('name')->required()->columnSpanFull(),
                Textarea::make('description')->rows(2)->columnSpanFull(),
                Select::make('trigger_type')->label('Auto-Enroll Trigger')->options([
                    'manual'        => 'Manual Only',
                    'lead_created'  => 'When Lead is Created',
                    'stage_changed' => 'When Lead Reaches Stage',
                ])->default('manual')->live(),
                Select::make('trigger_stage')
                    ->label('Target Stage')
                    ->options([
                        'new_lead'          => 'New Lead',
                        'no_contact'        => 'No Contact',
                        'contact_made'      => 'Contact Made',
                        'appointment_set'   => 'Appointment Set',
                        'due_diligence'     => 'Due Diligence',
                        'offer_made'        => 'Offer Made',
                        'under_contract'    => 'Under Contract',
                    ])
                    ->visible(fn ($get) => $get('trigger_type') === 'stage_changed'),
                Toggle::make('is_active')->label('Active')->default(true)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                TextColumn::make('trigger_type')->label('Trigger')->badge()->color(fn ($state) => match ($state) {
                    'lead_created'  => 'info',
                    'stage_changed' => 'warning',
                    default         => 'gray',
                })->formatStateUsing(fn ($state) => match ($state) {
                    'manual'        => 'Manual',
                    'lead_created'  => 'Lead Created',
                    'stage_changed' => 'Stage Change',
                    default         => $state,
                }),
                TextColumn::make('steps_count')->label('Steps')->counts('steps')->badge()->color('primary'),
                TextColumn::make('enrollments_count')->label('Enrolled')->counts('enrollments')->badge()->color('success'),
                IconColumn::make('is_active')->label('Active')->boolean(),
                TextColumn::make('created_at')->label('Created')->date()->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (DripCampaign $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (DripCampaign $record) => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->color(fn (DripCampaign $record) => $record->is_active ? 'warning' : 'success')
                    ->action(fn (DripCampaign $record) => $record->update(['is_active' => !$record->is_active])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelationManagers(): array
    {
        return [StepsRelationManager::class, EnrollmentsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDripCampaigns::route('/'),
            'create' => Pages\CreateDripCampaign::route('/create'),
            'edit'   => Pages\EditDripCampaign::route('/{record}/edit'),
            'view'   => Pages\ViewDripCampaign::route('/{record}'),
        ];
    }
}
