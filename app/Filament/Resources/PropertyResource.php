<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Filament\Resources\PropertyResource\RelationManagers\TasksRelationManager;
use App\Filament\Resources\PropertyResource\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\PropertyResource\RelationManagers\ActivitiesRelationManager;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Location')->columns(2)->schema([
                    TextInput::make('address')->required(),
                    TextInput::make('city')->required(),
                    TextInput::make('state')->required(),
                    TextInput::make('zip')->required(),
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
                        'rental' => 'Rental',
                        'sold' => 'Sold',
                        'dead' => 'Dead',
                    ])->default('prospect'),
                ]),
                Forms\Components\Section::make('Details')->columns(3)->schema([
                    TextInput::make('beds')->numeric(),
                    TextInput::make('baths')->numeric(),
                    TextInput::make('kitchens')->numeric(),
                    TextInput::make('sqft')->numeric(),
                    TextInput::make('year_built')->numeric(),
                    TextInput::make('lot_size')->numeric(),
                    TextInput::make('basement_type'),
                    TextInput::make('basement_area')->numeric(),
                    TextInput::make('garage_type'),
                    TextInput::make('garage_area')->numeric(),
                ]),
                Forms\Components\Section::make('Financials')->columns(3)->schema([
                    TextInput::make('arv')->numeric(),
                    TextInput::make('estimated_repairs')->numeric(),
                    TextInput::make('estimated_rent')->numeric(),
                    TextInput::make('acquisition_price')->numeric(),
                    TextInput::make('sale_price')->numeric(),
                    TextInput::make('estimated_value')->numeric(),
                    TextInput::make('estimated_total_liens')->numeric(),
                    TextInput::make('estimated_equity')->numeric(),
                ]),
                Forms\Components\Section::make('Tax')->columns(3)->schema([
                    TextInput::make('total_assessed_value')->numeric(),
                    TextInput::make('assessed_land_value')->numeric(),
                    TextInput::make('assessed_improvement_value')->numeric(),
                    TextInput::make('assessed_year')->numeric(),
                    TextInput::make('tax_year')->numeric(),
                    TextInput::make('property_taxes')->numeric(),
                    TextInput::make('annual_taxes')->numeric(),
                    TextInput::make('annual_insurance')->numeric(),
                ]),
                Forms\Components\Section::make('Mortgage')->columns(3)->schema([
                    TextInput::make('mortgage_amount')->numeric(),
                    TextInput::make('mortgage_type'),
                    TextInput::make('interest_rate')->numeric(),
                    TextInput::make('mortgage_term')->numeric(),
                    DatePicker::make('original_loan_date'),
                    DatePicker::make('mortgage_maturity_date'),
                    TextInput::make('lender_name'),
                    TextInput::make('current_loan_balance')->numeric(),
                ]),
                Forms\Components\Section::make('Last Sale')->columns(3)->schema([
                    DatePicker::make('last_sale_date'),
                    TextInput::make('ownership_duration_months')->numeric(),
                    TextInput::make('last_sale_amount')->numeric(),
                ]),
                Forms\Components\Section::make('MLS')->columns(3)->schema([
                    TextInput::make('mls_status'),
                    DatePicker::make('mls_listing_date'),
                    TextInput::make('mls_price')->numeric(),
                    TextInput::make('mls_listing_type'),
                    TextInput::make('mls_days_on_market')->numeric(),
                    TextInput::make('agent_name'),
                    TextInput::make('agent_phone'),
                    TextInput::make('agent_email'),
                ]),
                Forms\Components\Section::make('Rental / Operations')->columns(3)->schema([
                    Checkbox::make('owner_financing')->label('Owner Financing'),
                    TextInput::make('ownership_duration_years')->numeric(),
                    TextInput::make('unit_count')->numeric(),
                    TextInput::make('utilities_metered'),
                    TextInput::make('utilities_payer'),
                    Checkbox::make('deferred_maintenance')->label('Deferred Maintenance'),
                    TextInput::make('lease_type'),
                    Textarea::make('unit_mix')->rows(2),
                    TextInput::make('debt_owed')->numeric(),
                    TextInput::make('vacancies')->numeric(),
                    TextInput::make('property_manager_name'),
                    DatePicker::make('lease_start_date'),
                    DatePicker::make('lease_end_date'),
                    TextInput::make('project_type'),
                    DatePicker::make('sold_date'),
                    TextInput::make('holding_period_days')->numeric(),
                ]),
                Forms\Components\Section::make('Relationships')->columns(2)->schema([
                    Select::make('lead_id')->label('Seller Lead')->options(fn () => Lead::orderBy('owner_name')->pluck('owner_name', 'id'))->searchable(),
                    Select::make('active_deal_id')->label('Active Deal')->options(fn () => Deal::orderBy('name')->pluck('name', 'id'))->searchable(),
                ]),
                Textarea::make('notes')->rows(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('address')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('city')->sortable()->visibleFrom('md'),
                Tables\Columns\BadgeColumn::make('status')->sortable(),
                Tables\Columns\TextColumn::make('type')->visibleFrom('md'),
                Tables\Columns\TextColumn::make('arv')->money('usd', true)->label('ARV')->visibleFrom('lg'),
                Tables\Columns\TextColumn::make('acquisition_price')->money('usd', true)->label('Buy')->visibleFrom('lg'),
                Tables\Columns\TextColumn::make('sale_price')->money('usd', true)->label('Sell')->visibleFrom('lg'),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'prospect' => 'Prospect',
                    'under_contract' => 'Under Contract',
                    'owned' => 'Owned',
                    'listed' => 'Listed',
                    'rental' => 'Rental',
                    'sold' => 'Sold',
                    'dead' => 'Dead',
                ]),
                SelectFilter::make('type')->options([
                    'sfr' => 'SFR',
                    'multi_family' => 'Multi-family',
                    'land' => 'Land',
                    'condo' => 'Condo',
                    'other' => 'Other',
                ]),
                SelectFilter::make('city')->options(fn () => Property::query()->whereNotNull('city')->distinct()->pluck('city', 'city')->toArray()),
                Filter::make('stale')->label('Stale (>30d no update)')->query(fn ($q) => $q->where('updated_at', '<', now()->subDays(30))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Action::make('export_csv')->label('Export CSV')->action(function ($records) {
                        $csv = implode(",", ['Address', 'City', 'Type', 'Status', 'ARV', 'Acquisition', 'Sale']) . "\n";
                        foreach ($records as $p) {
                            $csv .= implode(",", [
                                $p->address,
                                $p->city,
                                $p->type,
                                $p->status,
                                $p->arv,
                                $p->acquisition_price,
                                $p->sale_price,
                            ]) . "\n";
                        }
                        return response()->streamDownload(function () use ($csv) {
                            echo $csv;
                        }, 'properties.csv');
                    }),
                    Action::make('import_csv')
                        ->label('Import CSV')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->form([
                            FileUpload::make('file')
                                ->required()
                                ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                                ->directory('imports'),
                        ])
                        ->action(function (array $data) {
                            $path = $data['file'];
                            $fullPath = Storage::disk(config('filesystems.default'))->path($path);
                            if (!file_exists($fullPath)) {
                                return;
                            }
                            $handle = fopen($fullPath, 'r');
                            if (!$handle) {
                                return;
                            }
                            $headers = null;
                            $imported = 0;
                            while (($row = fgetcsv($handle)) !== false) {
                                if ($headers === null) {
                                    $headers = $row;
                                    continue;
                                }
                                $rowData = array_combine($headers, $row);
                                if (!$rowData) {
                                    continue;
                                }
                                $payload = [
                                    'team_id' => auth()->user()?->team_id,
                                    'address' => $rowData['address'] ?? null,
                                    'city' => $rowData['city'] ?? null,
                                    'state' => $rowData['state'] ?? null,
                                    'zip' => $rowData['zip'] ?? null,
                                    'type' => $rowData['type'] ?? 'sfr',
                                    'status' => $rowData['status'] ?? 'prospect',
                                    'arv' => $rowData['arv'] ?? null,
                                    'acquisition_price' => $rowData['acquisition_price'] ?? null,
                                    'sale_price' => $rowData['sale_price'] ?? null,
                                ];
                                Property::create($payload);
                                $imported++;
                            }
                            fclose($handle);
                            session()->flash('notification', "Imported {$imported} property(ies).");
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TasksRelationManager::class,
            AttachmentsRelationManager::class,
            ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'view' => Pages\ViewProperty::route('/{record}'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}
