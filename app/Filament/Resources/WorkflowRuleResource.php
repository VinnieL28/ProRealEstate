<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkflowRuleResource\Pages;
use App\Models\WorkflowRule;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkflowRuleResource extends Resource
{
    protected static ?string $model = WorkflowRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Workflow Rules';

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'admin'], true);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Rule Setup')->columns(2)->schema([
                TextInput::make('name')->required(),
                Select::make('trigger')
                    ->options([
                        'lead_created'  => 'Lead Created',
                        'stage_changed' => 'Lead Stage Changed',
                        'deal_closed'   => 'Deal Closed (Won)',
                    ])
                    ->required(),
                Toggle::make('is_active')->label('Active')->default(true)->columnSpanFull(),
            ]),

            Section::make('Conditions')
                ->description('All conditions must match for the rule to fire. Leave empty to fire unconditionally.')
                ->schema([
                    Repeater::make('conditions')
                        ->schema([
                            TextInput::make('field')->placeholder('e.g. stage, motivation_level')->required(),
                            Select::make('operator')->options([
                                '='  => 'equals (=)',
                                '!=' => 'not equals (!=)',
                                '>'  => 'greater than (>)',
                                '>=' => 'greater than or equal (>=)',
                                '<'  => 'less than (<)',
                                '<=' => 'less than or equal (<=)',
                            ])->default('='),
                            TextInput::make('value')->placeholder('e.g. closed_won')->required(),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->addActionLabel('Add Condition'),
                ]),

            Section::make('Actions')
                ->description('Actions to execute when the rule fires.')
                ->schema([
                    Repeater::make('actions')
                        ->schema([
                            Select::make('type')
                                ->options([
                                    'send_email'   => 'Send Email',
                                    'send_sms'     => 'Send SMS',
                                    'create_task'  => 'Create Task',
                                    'assign_agent' => 'Assign Agent',
                                ])
                                ->required()
                                ->reactive(),
                            TextInput::make('subject')->label('Subject (email)')->placeholder('Email subject'),
                            TextInput::make('body')->label('Body / Message')->placeholder('Email body or SMS message'),
                            TextInput::make('title')->label('Task Title')->placeholder('Task title'),
                            TextInput::make('due_in_days')->label('Task Due (days)')->numeric()->default(1),
                            Select::make('priority')->label('Task Priority')->options([
                                'low' => 'Low', 'medium' => 'Medium', 'high' => 'High',
                            ])->default('medium'),
                        ])
                        ->columns(3)
                        ->defaultItems(1)
                        ->addActionLabel('Add Action'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('trigger')->formatStateUsing(fn ($s) => match ($s) {
                    'lead_created'  => 'Lead Created',
                    'stage_changed' => 'Stage Changed',
                    'deal_closed'   => 'Deal Closed',
                    default => $s,
                }),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\TextColumn::make('updated_at')->since()->label('Updated'),
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
            'index'  => Pages\ListWorkflowRules::route('/'),
            'create' => Pages\CreateWorkflowRule::route('/create'),
            'edit'   => Pages\EditWorkflowRule::route('/{record}/edit'),
        ];
    }
}
