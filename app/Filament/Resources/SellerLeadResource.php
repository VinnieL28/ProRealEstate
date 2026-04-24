<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SellerLeadResource\Pages;
use App\Filament\Resources\SellerLeadResource\RelationManagers\OffersRelationManager;
use App\Models\Contact;
use App\Models\SellerLead;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SellerLeadResource extends Resource
{
    protected static ?string $model = SellerLead::class;
    protected static ?string $navigationIcon  = 'heroicon-o-home-modern';
    protected static ?string $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Seller Leads';
    protected static ?int    $navigationSort  = 4;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();

        if ($user && $user->team_id) {
            $query->whereHas('contact', fn ($q) => $q->where('team_id', $user->team_id));
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Seller Lead Info')->columns(2)->schema([
                Select::make('contact_id')
                    ->label('Contact')
                    ->options(fn () => Contact::whereNotNull('first_name')
                        ->when(auth()->user()?->team_id, fn ($q, $tid) => $q->where('team_id', $tid))
                        ->get()
                        ->mapWithKeys(fn ($c) => [$c->id => $c->full_name . ' — ' . ($c->phone_primary ?? $c->email ?? '')]))
                    ->searchable()
                    ->required(),
                TextInput::make('property_address')->label('Property Address')->columnSpanFull(),
                TextInput::make('campaign_name')->label('Campaign'),
                Select::make('lead_source')->label('Source')->options([
                    'Cold Call' => 'Cold Call',
                    'Referral'  => 'Referral',
                    'PPC'       => 'PPC',
                    'SMS'       => 'SMS',
                    'D4D'       => 'D4D',
                    'Direct Mail' => 'Direct Mail',
                    'Other'     => 'Other',
                ]),
                Select::make('status')->options([
                    'new'            => 'New',
                    'contacted'      => 'Contacted',
                    'appointment'    => 'Appointment Set',
                    'offer_sent'     => 'Offer Sent',
                    'under_contract' => 'Under Contract',
                    'closed'         => 'Closed',
                    'dead'           => 'Dead',
                ])->default('new'),
                Select::make('drip_status')->label('Drip Status')->options([
                    'new'       => 'New',
                    'active'    => 'Active',
                    'paused'    => 'Paused',
                    'completed' => 'Completed',
                ])->default('new'),
            ]),
            Section::make('Tags & Notes')->schema([
                TagsInput::make('tags')->columnSpanFull(),
                Textarea::make('notes')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contact.full_name')->label('Seller')->searchable(['contacts.first_name', 'contacts.last_name'])->weight('bold'),
                TextColumn::make('property_address')->label('Property')->searchable()->limit(35),
                TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'new'            => 'info',
                    'contacted'      => 'warning',
                    'appointment'    => 'primary',
                    'offer_sent'     => 'warning',
                    'under_contract' => 'success',
                    'closed'         => 'success',
                    'dead'           => 'danger',
                    default          => 'gray',
                }),
                TextColumn::make('lead_source')->label('Source')->badge()->color('gray'),
                TextColumn::make('campaign_name')->label('Campaign')->limit(20),
                TextColumn::make('offers_count')->label('Offers')->counts('offers')->badge()->color('primary'),
                TextColumn::make('created_at')->label('Added')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'new'            => 'New',
                    'contacted'      => 'Contacted',
                    'appointment'    => 'Appointment Set',
                    'offer_sent'     => 'Offer Sent',
                    'under_contract' => 'Under Contract',
                    'closed'         => 'Closed',
                    'dead'           => 'Dead',
                ]),
                SelectFilter::make('lead_source')->label('Source')->options([
                    'Cold Call'   => 'Cold Call',
                    'Referral'    => 'Referral',
                    'PPC'         => 'PPC',
                    'SMS'         => 'SMS',
                    'D4D'         => 'D4D',
                    'Direct Mail' => 'Direct Mail',
                    'Other'       => 'Other',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('add_offer')
                    ->label('Add Offer')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->url(fn (SellerLead $record) => SellerLeadResource::getUrl('view', ['record' => $record]) . '#offers'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelationManagers(): array
    {
        return [OffersRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSellerLeads::route('/'),
            'create' => Pages\CreateSellerLead::route('/create'),
            'edit'   => Pages\EditSellerLead::route('/{record}/edit'),
            'view'   => Pages\ViewSellerLead::route('/{record}'),
        ];
    }
}
