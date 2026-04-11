<?php

namespace App\Filament\Resources;

use App\Exports\DealsExport;
use App\Filament\Resources\DealResource\Pages;
use App\Filament\Resources\DealResource\RelationManagers\TasksRelationManager;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use App\Filament\Resources\DealResource\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\DealResource\RelationManagers\ActivitiesRelationManager;

class DealResource extends Resource
{
    protected static ?string $model = Deal::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Deals';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Agents only see deals linked to their leads
        if ($user && in_array($user->role, ['cold_caller', 'real_estate_agent'], true)) {
            $query->whereHas('lead', fn ($q) => $q->where('assigned_to_id', $user->id));
        } elseif ($user && $user->team_id) {
            $query->where('deals.team_id', $user->team_id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Deal')->columns(2)->schema([
                    TextInput::make('name')->required(),
                    Select::make('property_id')->label('Property')->options(fn () => Property::orderBy('address')->whereNotNull('address')->pluck('address', 'id')->toArray())->searchable()->required(),
                    Select::make('lead_id')->label('Seller Lead')->options(fn () => Lead::orderBy('owner_name')->whereNotNull('owner_name')->pluck('owner_name', 'id')->toArray())->searchable(),
                    Select::make('contract_type')->options([
                        'assignment' => 'Assignment',
                        'double_close' => 'Double Close',
                        'wholetail' => 'Wholetail',
                    ])->default('assignment'),
                    Select::make('stage')->options([
                        'analyzing' => 'Analyzing',
                        'contract_sent' => 'Contract Sent',
                        'under_contract' => 'Under Contract',
                        'clear_to_close' => 'Clear to Close',
                        'closed_won' => 'Closed Won',
                        'closed_lost' => 'Closed Lost',
                    ])->default('analyzing'),
                ]),
                Section::make('Dates')->columns(2)->schema([
                    DatePicker::make('contract_date'),
                    DatePicker::make('closing_date'),
                ]),
                Section::make('Financials')->columns(3)->schema([
                    TextInput::make('purchase_price')->numeric(),
                    TextInput::make('assignment_fee')->numeric(),
                    TextInput::make('sale_price')->numeric(),
                    TextInput::make('closing_costs')->numeric(),
                    TextInput::make('marketing_costs')->numeric(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('property.address')->label('Property')->hiddenOn('sm'),
                Tables\Columns\TextColumn::make('lead.owner_name')->label('Seller')->hiddenOn('sm'),
                Tables\Columns\BadgeColumn::make('stage')->sortable(),
                Tables\Columns\TextColumn::make('purchase_price')->money('usd', true)->label('Buy')->hiddenOn(['sm', 'md']),
                Tables\Columns\TextColumn::make('sale_price')->money('usd', true)->label('Sell')->hiddenOn('sm'),
                Tables\Columns\TextColumn::make('profit')->money('usd', true)->label('Profit'),
                Tables\Columns\TextColumn::make('roi')->suffix('%')->label('ROI')->hiddenOn(['sm', 'md']),
                Tables\Columns\TextColumn::make('closing_date')->date()->hiddenOn('sm'),
            ])
            ->filters([
                SelectFilter::make('stage')->options([
                    'analyzing' => 'Analyzing',
                    'contract_sent' => 'Contract Sent',
                    'under_contract' => 'Under Contract',
                    'clear_to_close' => 'Clear to Close',
                    'closed_won' => 'Closed Won',
                    'closed_lost' => 'Closed Lost',
                ]),
                Filter::make('closing_date')->form([
                    DatePicker::make('from'),
                    DatePicker::make('until'),
                ])->query(function (Builder $query, array $data) {
                    return $query
                        ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('closing_date', '>=', $date))
                        ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('closing_date', '<=', $date));
                }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('send_esign')
                    ->label('Send for Signature')
                    ->icon('heroicon-o-document-text')
                    ->requiresConfirmation()
                    ->action(function (Deal $record) {
                        $record->update([
                            'esign_provider' => 'docusign',
                            'esign_status' => 'sent',
                            'esign_sent_at' => now(),
                            'esign_envelope_id' => $record->esign_envelope_id ?? strtoupper(bin2hex(random_bytes(5))),
                        ]);
                        Notification::make()->title('E-signature sent (stub)')->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('export_excel')
                        ->label('Export Excel')
                        ->icon('heroicon-o-table-cells')
                        ->action(fn () => Excel::download(
                            new DealsExport(auth()->user()?->team_id),
                            'deals-' . now()->format('Ymd') . '.xlsx'
                        )),
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
            'index' => Pages\ListDeals::route('/'),
            'create' => Pages\CreateDeal::route('/create'),
            'view' => Pages\ViewDeal::route('/{record}'),
            'edit' => Pages\EditDeal::route('/{record}/edit'),
        ];
    }
}
