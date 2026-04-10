<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Team;
use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Team Members';

    protected static ?int $navigationSort = 10;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['owner', 'admin'], true);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Identity')->columns(2)->schema([
                TextInput::make('first_name')->label('First Name'),
                TextInput::make('last_name')->label('Last Name'),
                TextInput::make('name')->label('Display Name')->required(),
                TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                TextInput::make('phone')->label('Phone'),
            ]),
            Section::make('Role & Team')->columns(2)->schema([
                Select::make('role')
                    ->options([
                        'owner'                => 'Owner',
                        'admin'                => 'Admin',
                        'acquisition_manager'  => 'Acquisition Manager',
                        'lead_manager'         => 'Lead Manager',
                        'cold_caller'          => 'Cold Caller',
                        'dispo_manager'        => 'Dispo Manager',
                    ])
                    ->required()
                    ->default('cold_caller'),
                Select::make('team_id')
                    ->label('Team')
                    ->options(fn () => Team::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
                Select::make('status')
                    ->options([
                        'active'   => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->default('active'),
                Select::make('employee_status')
                    ->label('Employment Status')
                    ->options([
                        'full_time'  => 'Full Time',
                        'part_time'  => 'Part Time',
                        'contractor' => 'Contractor',
                    ]),
            ]),
            Section::make('Password')->schema([
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context) => $context === 'create')
                    ->label(fn (string $context) => $context === 'create' ? 'Password' : 'New Password (leave blank to keep)'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->visibleFrom('md'),
                Tables\Columns\BadgeColumn::make('role')
                    ->colors([
                        'danger'  => 'owner',
                        'warning' => 'admin',
                        'info'    => 'acquisition_manager',
                        'primary' => 'lead_manager',
                        'gray'    => 'cold_caller',
                        'success' => 'dispo_manager',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger'  => 'inactive',
                    ])
                    ->visibleFrom('sm'),
                Tables\Columns\TextColumn::make('team.name')->label('Team')->visibleFrom('md'),
                Tables\Columns\TextColumn::make('phone')->visibleFrom('lg'),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Joined')->visibleFrom('lg'),
            ])
            ->filters([
                SelectFilter::make('role')->options([
                    'owner'               => 'Owner',
                    'admin'               => 'Admin',
                    'acquisition_manager' => 'Acquisition Manager',
                    'lead_manager'        => 'Lead Manager',
                    'cold_caller'         => 'Cold Caller',
                    'dispo_manager'       => 'Dispo Manager',
                ]),
                SelectFilter::make('status')->options([
                    'active'   => 'Active',
                    'inactive' => 'Inactive',
                ]),
                SelectFilter::make('team_id')
                    ->label('Team')
                    ->options(fn () => Team::orderBy('name')->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (User $record) => $record->id === auth()->id()),
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
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
