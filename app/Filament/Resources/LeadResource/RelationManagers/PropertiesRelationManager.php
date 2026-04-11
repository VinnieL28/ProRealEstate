<?php

namespace App\Filament\Resources\LeadResource\RelationManagers;

use App\Models\Property;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class PropertiesRelationManager extends RelationManager
{
    protected static string $relationship = 'properties';

    protected static ?string $title = 'Linked Properties';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('address')->required(),
                TextInput::make('city'),
                TextInput::make('state'),
                TextInput::make('zip'),
                Select::make('type')->options([
                    'sfr'          => 'SFR',
                    'multi_family' => 'Multi-family',
                    'land'         => 'Land',
                    'condo'        => 'Condo',
                    'other'        => 'Other',
                ])->default('sfr'),
                Select::make('status')->options([
                    'prospect'       => 'Prospect',
                    'under_contract' => 'Under Contract',
                    'owned'          => 'Owned',
                    'listed'         => 'Listed',
                    'sold'           => 'Sold',
                    'dead'           => 'Dead',
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
                Tables\Columns\TextColumn::make('arv')->money('usd', true)->label('ARV'),
            ])
            ->headerActions([
                // Auto-find matching properties and offer to link them
                Action::make('find_matches')
                    ->label('Find Matching Properties')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('info')
                    ->action(function () {
                        $lead    = $this->ownerRecord;
                        $matches = $lead->matchedPropertiesQuery()
                            ->whereNull('lead_id')
                            ->limit(10)
                            ->get();

                        if ($matches->isEmpty()) {
                            Notification::make()
                                ->title('No unlinked properties match this lead\'s budget/market')
                                ->info()
                                ->send();
                            return;
                        }

                        foreach ($matches as $property) {
                            $property->update(['lead_id' => $lead->id]);
                        }

                        Notification::make()
                            ->title("Linked {$matches->count()} matching propert" . ($matches->count() === 1 ? 'y' : 'ies') . ' to this lead')
                            ->success()
                            ->send();
                    }),

                // Manually link an existing property
                Action::make('link_property')
                    ->label('Link Existing Property')
                    ->icon('heroicon-o-link')
                    ->color('gray')
                    ->form([
                        Select::make('property_id')
                            ->label('Property')
                            ->options(fn () => Property::whereNull('lead_id')
                                ->where('team_id', $this->ownerRecord->team_id)
                                ->orderBy('address')
                                ->whereNotNull('address')
                                ->pluck('address', 'id')
                                ->toArray())
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $property = Property::find($data['property_id']);
                        if ($property) {
                            $property->update(['lead_id' => $this->ownerRecord->id]);
                            Notification::make()->title('Property linked to lead')->success()->send();
                        }
                    }),

                Tables\Actions\CreateAction::make()
                    ->label('New Property')
                    ->mutateFormDataUsing(function (array $data): array {
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
                // Unlink (removes lead_id without deleting the property)
                Action::make('unlink')
                    ->label('Unlink')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Property $record) {
                        $record->update(['lead_id' => null]);
                        Notification::make()->title('Property unlinked from lead')->success()->send();
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
