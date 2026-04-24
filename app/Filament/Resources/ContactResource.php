<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Models\Contact;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;
    protected static ?string $navigationIcon  = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Contacts';
    protected static ?int    $navigationSort  = 2;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();

        if ($user && $user->team_id) {
            $query->where('team_id', $user->team_id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Personal Info')->columns(3)->schema([
                TextInput::make('first_name')->required(),
                TextInput::make('last_name'),
                TextInput::make('company'),
                TextInput::make('email')->email(),
                TextInput::make('phone_primary')->label('Primary Phone')->tel(),
                TextInput::make('phone_secondary')->label('Secondary Phone')->tel(),
                Select::make('status')->options([
                    'active'    => 'Active',
                    'inactive'  => 'Inactive',
                    'do_not_contact' => 'Do Not Contact',
                ])->default('active'),
                Select::make('source')->options([
                    'Cold Call'  => 'Cold Call',
                    'Referral'   => 'Referral',
                    'PPC'        => 'PPC',
                    'SMS'        => 'SMS',
                    'Agent'      => 'Agent',
                    'D4D'        => 'D4D',
                    'Other'      => 'Other',
                ]),
                Hidden::make('team_id')->default(fn () => auth()->user()?->team_id),
            ]),
            Section::make('Address')->columns(2)->schema([
                TextInput::make('address_line1')->label('Address Line 1')->columnSpanFull(),
                TextInput::make('address_line2')->label('Address Line 2')->columnSpanFull(),
                TextInput::make('city'),
                TextInput::make('state'),
                TextInput::make('postal_code')->label('ZIP / Postal Code'),
            ]),
            Section::make('Notes')->schema([
                Textarea::make('notes')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')->label('Name')->searchable(['first_name', 'last_name', 'company'])->sortable()->weight('bold'),
                TextColumn::make('email')->searchable()->copyable(),
                TextColumn::make('phone_primary')->label('Phone')->searchable(),
                TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'active'         => 'success',
                    'inactive'       => 'gray',
                    'do_not_contact' => 'danger',
                    default          => 'gray',
                }),
                TextColumn::make('source')->badge()->color('primary'),
                TextColumn::make('city')->sortable(),
                TextColumn::make('created_at')->label('Added')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'active'         => 'Active',
                    'inactive'       => 'Inactive',
                    'do_not_contact' => 'Do Not Contact',
                ]),
                SelectFilter::make('source')->options([
                    'Cold Call' => 'Cold Call',
                    'Referral'  => 'Referral',
                    'PPC'       => 'PPC',
                    'SMS'       => 'SMS',
                    'Agent'     => 'Agent',
                    'D4D'       => 'D4D',
                    'Other'     => 'Other',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit'   => Pages\EditContact::route('/{record}/edit'),
            'view'   => Pages\ViewContact::route('/{record}'),
        ];
    }
}
