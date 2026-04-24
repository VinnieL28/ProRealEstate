<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ColdLeadResource\Pages;
use App\Models\ColdLead;
use App\Models\Contact;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class ColdLeadResource extends Resource
{
    protected static ?string $model = ColdLead::class;
    protected static ?string $navigationIcon  = 'heroicon-o-phone-arrow-down-left';
    protected static ?string $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Cold Leads';
    protected static ?int    $navigationSort  = 3;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();

        if ($user && in_array($user->role, ['cold_caller'], true)) {
            $query->where('team_assigned', $user->id);
        } elseif ($user && $user->team_id) {
            $query->whereHas('contact', fn ($q) => $q->where('team_id', $user->team_id));
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Cold Lead Info')->columns(2)->schema([
                Select::make('contact_id')
                    ->label('Contact')
                    ->options(fn () => Contact::whereNotNull('first_name')
                        ->when(auth()->user()?->team_id, fn ($q, $tid) => $q->where('team_id', $tid))
                        ->get()
                        ->mapWithKeys(fn ($c) => [$c->id => $c->full_name . ' — ' . ($c->phone_primary ?? $c->email ?? '')]))
                    ->searchable()
                    ->required(),
                TextInput::make('campaign_name')->label('Campaign'),
                Select::make('status')->options([
                    'new'               => 'New',
                    'contact_attempted' => 'Contact Attempted',
                    'contacted'         => 'Contacted',
                    'not_interested'    => 'Not Interested',
                    'do_not_call'       => 'Do Not Call',
                    'converted'         => 'Converted',
                ])->default('new'),
                Select::make('drip_status')->label('Drip Status')->options([
                    'new'               => 'New',
                    'contact_attempted' => 'In Progress',
                    'paused'            => 'Paused',
                    'completed'         => 'Completed',
                ])->default('new'),
                TextInput::make('drip_step')->label('Drip Step')->numeric()->default(0),
                TextInput::make('team_assigned')->label('Assigned User ID')->numeric(),
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
                TextColumn::make('contact.full_name')->label('Contact')->searchable(['contacts.first_name', 'contacts.last_name'])->sortable()->weight('bold'),
                TextColumn::make('contact.phone_primary')->label('Phone'),
                TextColumn::make('campaign_name')->label('Campaign')->badge()->color('primary'),
                TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'new'               => 'info',
                    'contact_attempted' => 'warning',
                    'contacted'         => 'success',
                    'not_interested'    => 'gray',
                    'do_not_call'       => 'danger',
                    'converted'         => 'success',
                    default             => 'gray',
                }),
                TextColumn::make('drip_status')->label('Drip')->badge()->color(fn ($state) => match ($state) {
                    'new'               => 'info',
                    'contact_attempted' => 'warning',
                    'paused'            => 'gray',
                    'completed'         => 'success',
                    default             => 'gray',
                }),
                TextColumn::make('drip_step')->label('Step')->sortable(),
                TextColumn::make('created_at')->label('Added')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'new'               => 'New',
                    'contact_attempted' => 'Contact Attempted',
                    'contacted'         => 'Contacted',
                    'not_interested'    => 'Not Interested',
                    'do_not_call'       => 'Do Not Call',
                    'converted'         => 'Converted',
                ]),
                SelectFilter::make('drip_status')->label('Drip Status')->options([
                    'new'               => 'New',
                    'contact_attempted' => 'In Progress',
                    'paused'            => 'Paused',
                    'completed'         => 'Completed',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('mark_contacted')
                    ->label('Contacted')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (ColdLead $record) {
                        $record->update(['status' => 'contacted']);
                        Notification::make()->title('Marked as contacted')->success()->send();
                    })
                    ->visible(fn (ColdLead $record) => $record->status !== 'contacted'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('bulk_contacted')
                        ->label('Mark Contacted')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['status' => 'contacted']))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListColdLeads::route('/'),
            'create' => Pages\CreateColdLead::route('/create'),
            'edit'   => Pages\EditColdLead::route('/{record}/edit'),
        ];
    }
}
