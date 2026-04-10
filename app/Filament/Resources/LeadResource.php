<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Filament\Resources\LeadResource\RelationManagers\CallLogsRelationManager;
use App\Filament\Resources\LeadResource\RelationManagers\PropertiesRelationManager;
use App\Filament\Resources\LeadResource\RelationManagers\SmsLogsRelationManager;
use App\Filament\Resources\LeadResource\RelationManagers\TasksRelationManager;
use App\Filament\Resources\LeadResource\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\LeadResource\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\LeadResource\RelationManagers\EmailLogsRelationManager;
use App\Models\Lead;
use App\Models\Deal;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea as FormsTextarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use App\Models\CallLog;
use App\Models\SmsLog;
use App\Services\TwilioService;
use App\Exports\LeadsExport;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Leads';

    protected static ?string $navigationLabel = 'Leads';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // cold_caller and real_estate_agent only see their own assigned leads
        if ($user && in_array($user->role, ['cold_caller', 'real_estate_agent'], true)) {
            $query->where('assigned_to_id', $user->id);
        } elseif ($user && $user->team_id) {
            $query->where('leads.team_id', $user->team_id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Contact Information')->columns(3)->schema([
                    TextInput::make('first_name')->label('First Name'),
                    TextInput::make('last_name')->label('Last Name'),
                    TextInput::make('owner_name')->label('Owner / Company'),
                    TextInput::make('primary_phone')->label('Primary Phone'),
                    TextInput::make('phone')->label('Alt Phone'),
                    TextInput::make('primary_email')->label('Primary Email')->email(),
                    TextInput::make('email')->label('Alt Email')->email(),
                    Select::make('lead_source')
                        ->options([
                            'Cold Call' => 'Cold Call',
                            'SMS' => 'SMS',
                            'PPC' => 'PPC',
                            'Referral' => 'Referral',
                            'Agent' => 'Agent',
                            'D4D' => 'Driving for Dollars',
                            'Other' => 'Other',
                        ]),
                    TextInput::make('major_market')->label('Major Market'),
                    Select::make('assigned_to_id')
                        ->label('Assigned To')
                        ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                        ->searchable(),
                    Select::make('active_deal_id')
                        ->label('Active Deal')
                        ->options(fn () => Deal::orderBy('name')->pluck('name', 'id'))
                        ->searchable(),
                    Select::make('stage')
                        ->required()
                        ->options([
                            'new_lead' => 'New Lead',
                            'no_contact' => 'No Contact Made',
                            'contact_made' => 'Contact Made',
                            'appointment_set' => 'Appointments Set',
                            'due_diligence' => 'Due Diligence',
                            'offer_made' => 'Offers Made',
                            'under_contract' => 'Under Contract',
                            'closed_won' => 'Closed Won',
                            'closed_lost' => 'Closed Lost',
                        ])
                        ->default('new_lead'),
                    ToggleButtons::make('motivation_level')
                        ->options([
                            1 => '1',
                            2 => '2',
                            3 => '3',
                            4 => '4',
                            5 => '5',
                        ])
                        ->inline()
                        ->label('Motivation (1-5)')
                        ->nullable(),
                ]),
            Section::make('Financials')->columns(3)->schema([
                TextInput::make('asking_price')->numeric(),
                TextInput::make('max_offer')->numeric(),
                TextInput::make('last_offer')->numeric(),
                TextInput::make('min_cash_offer')->numeric()->label('Min Cash Offer'),
                TextInput::make('mortgage_on_house')->numeric()->label('Mortgage on house?'),
            ]),
            Section::make('Motivation & Status')->columns(2)->schema([
                Select::make('occupancy_status')->options([
                    'vacant' => 'Vacant',
                    'occupied' => 'Occupied',
                ]),
                Select::make('sell_timeline')->options([
                    'asap' => 'ASAP',
                    'next_few_months' => 'Next few months',
                    'when_right_offer' => 'When right offer',
                    'not_sure' => 'Not sure',
                    'other' => 'Other',
                ]),
                Select::make('sell_reason')->label('Why sell?')->options([
                    'behind_on_taxes' => 'Behind on Taxes/Mortgage',
                    'death_in_family' => 'Death in the Family',
                    'deferred_maintenance' => 'Deferred Maintenance',
                    'landlord_tired' => 'Don’t want to be a Landlord Anymore',
                    'downsizing' => 'Downsizing/Empty Nest',
                    'divorce' => 'Getting Divorced',
                    'health_issues' => 'Health Issues',
                    'job_loss' => 'Job Loss',
                    'moving_city' => 'Moving to a Different City',
                    'need_money' => 'Need Money',
                    'neighborhood' => 'Neighbourhood Changing',
                    'other_personal' => 'Other Personal Reason',
                    'other' => 'Other',
                ]),
                Select::make('listed_with_agent')->label('Listed with agent?')->options([
                    1 => 'Yes',
                    0 => 'No',
                ])->nullable(),
                Select::make('past_due_notice')->label('Past due notice?')->options([
                    1 => 'Yes',
                    0 => 'No',
                ])->nullable(),
            ]),
            Section::make('Rental Details')->columns(2)->schema([
                Checkbox::make('open_to_owner_financing')->label('Open to owner financing?'),
                TextInput::make('ownership_duration')->label('How long have you owned?'),
                TextInput::make('rental_unit_count')->numeric()->label('Number of units'),
                TextInput::make('annual_taxes')->numeric()->label('Annual taxes'),
                TextInput::make('annual_insurance')->numeric()->label('Annual insurance'),
                Select::make('utilities_metered')->label('Utilities separately metered?')->options([
                    'yes' => 'Yes',
                    'no' => 'No',
                    'partial' => 'Partial',
                ]),
                TextInput::make('utilities_payer')->label('Who pays utilities?'),
                Textarea::make('unit_mix')->rows(2),
                Select::make('tenant_lease_type')->label('Tenants on term or MTM?')->options([
                    'term' => 'Term leases',
                    'mtm' => 'Month to month',
                    'mixed' => 'Mixed',
                ]),
                TextInput::make('vacancies')->numeric()->label('Vacancies'),
                Checkbox::make('deferred_maintenance')->label('Deferred Maintenance'),
                TextInput::make('property_manager_name')->label('Who manages the property?'),
            ]),
            Section::make('Property Details')->columns(2)->schema([
                TextInput::make('property_address')->label('Property Address'),
                TextInput::make('owner_mailing_address')->label('Owner Mailing Address'),
            ]),
            Section::make('Tags & Notes')->schema([
                TagsInput::make('tags'),
                Textarea::make('notes')->rows(4),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('owner_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->searchable()->hiddenOn('sm'),
                Tables\Columns\TextColumn::make('lead_source')->sortable()->hiddenOn('sm'),
                Tables\Columns\TextColumn::make('major_market')->label('Market')->hiddenOn(['sm', 'md']),
                Tables\Columns\BadgeColumn::make('stage')->colors([
                    'primary' => 'new_lead',
                    'warning' => 'no_contact',
                    'info' => 'contact_made',
                    'success' => 'closed_won',
                    'danger' => 'closed_lost',
                ])->label('Stage')->sortable(),
                Tables\Columns\BadgeColumn::make('motivation_level')
                    ->label('Motivation')
                    ->colors([
                        'gray'    => fn ($state) => $state <= 2,
                        'warning' => fn ($state) => $state === 3,
                        'danger'  => fn ($state) => $state >= 4,
                    ])
                    ->formatStateUsing(fn ($state) => match ((int) $state) {
                        1 => '● 1',
                        2 => '● 2',
                        3 => '● 3',
                        4 => '🔥 4',
                        5 => '🔥 5',
                        default => '—',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('hot_score')
                    ->label('Score')
                    ->sortable(query: fn ($query, $direction) => $query->orderByRaw(
                        "(motivation_level * 2
                          + CASE WHEN sell_timeline = 'asap' THEN 2 ELSE 0 END
                          + CASE WHEN past_due_notice = 1 THEN 1.5 ELSE 0 END
                          + CASE WHEN deferred_maintenance = 1 THEN 0.5 ELSE 0 END
                          + CASE WHEN listed_with_agent = 1 THEN -2 ELSE 0 END
                         ) $direction"
                    ))
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . ' / 10')
                    ->color(fn ($state) => $state >= 7 ? 'danger' : ($state >= 5 ? 'warning' : 'gray')),
                Tables\Columns\TextColumn::make('assignedTo.name')->label('Assigned')->sortable()->hiddenOn('sm'),
                Tables\Columns\TextColumn::make('updated_at')->since()->label('Last touch')->hiddenOn('sm'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('M d, Y')->label('Created')->hiddenOn(['sm', 'md']),
            ])
            ->filters([
                SelectFilter::make('stage')->options([
                    'new_lead' => 'New Lead',
                    'no_contact' => 'No Contact Made',
                    'contact_made' => 'Contact Made',
                    'appointment_set' => 'Appointments Set',
                    'due_diligence' => 'Due Diligence',
                    'offer_made' => 'Offers Made',
                    'under_contract' => 'Under Contract',
                    'closed_won' => 'Closed Won',
                    'closed_lost' => 'Closed Lost',
                ]),
                SelectFilter::make('lead_source')->options([
                    'Cold Call' => 'Cold Call',
                    'SMS' => 'SMS',
                    'PPC' => 'PPC',
                    'Referral' => 'Referral',
                    'Agent' => 'Agent',
                    'D4D' => 'Driving for Dollars',
                    'Other' => 'Other',
                ]),
                SelectFilter::make('major_market')
                    ->label('Market')
                    ->options(fn () => Lead::query()->whereNotNull('major_market')->distinct()->pluck('major_market', 'major_market')->toArray()),
                SelectFilter::make('motivation_level')->options([
                    1 => '1',
                    2 => '2',
                    3 => '3',
                    4 => '4',
                    5 => '5',
                ]),
                SelectFilter::make('assigned_to_id')
                    ->label('Assigned To')
                    ->options(fn () => User::orderBy('name')->pluck('name', 'id')),
                Filter::make('stale')->label('Stale (>14d no touch)')->query(fn ($q) => $q->where('updated_at', '<', Carbon::now()->subDays(14))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('log_call')
                    ->label('Quick Call')
                    ->icon('heroicon-o-phone')
                    ->form([
                        TextInput::make('duration_minutes')->numeric()->label('Duration (min)'),
                        Select::make('outcome')->options([
                            'no_answer' => 'No Answer',
                            'left_vm' => 'Left VM',
                            'spoke' => 'Spoke',
                            'follow_up' => 'Follow-up Needed',
                        ])->default('spoke'),
                        Textarea::make('notes')->rows(2),
                    ])
                    ->action(function (array $data, Lead $record) {
                        CallLog::create([
                            'team_id' => auth()->user()?->team_id,
                            'lead_id' => $record->id,
                            'user_id' => auth()->id(),
                            'called_at' => now(),
                            'duration_minutes' => $data['duration_minutes'] ?? 0,
                            'outcome' => $data['outcome'] ?? 'spoke',
                            'notes' => $data['notes'] ?? null,
                        ]);
                    }),
                Tables\Actions\Action::make('log_sms')
                    ->label('Quick SMS')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->form([
                        Select::make('direction')->options([
                            'outbound' => 'Outbound',
                            'inbound' => 'Inbound',
                        ])->default('outbound'),
                        Textarea::make('message')->rows(2)->required(),
                        Textarea::make('notes')->rows(2),
                    ])
                    ->action(function (array $data, Lead $record) {
                        $direction = $data['direction'] ?? 'outbound';

                        SmsLog::create([
                            'team_id'   => auth()->user()?->team_id,
                            'lead_id'   => $record->id,
                            'user_id'   => auth()->id(),
                            'sent_at'   => now(),
                            'direction' => $direction,
                            'message'   => $data['message'],
                            'notes'     => $data['notes'] ?? null,
                        ]);

                        if ($direction === 'outbound') {
                            $phone = $record->primary_phone ?? $record->phone;
                            if ($phone) {
                                $sent = app(TwilioService::class)->sendSms($phone, $data['message']);
                                if (!$sent) {
                                    Notification::make()
                                        ->title('SMS logged, but Twilio delivery failed — check credentials in Settings.')
                                        ->warning()
                                        ->send();
                                    return;
                                }
                            }
                        }

                        Notification::make()->title('SMS logged successfully.')->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Action::make('export_csv')
                        ->label('Export CSV')
                        ->action(function ($records) {
                            $csv = implode(",", ['Owner Name', 'Email', 'Phone', 'Stage']) . "\n";
                            foreach ($records as $lead) {
                                $csv .= implode(",", [
                                    '"' . str_replace('"', '""', $lead->owner_name ?? '') . '"',
                                    '"' . str_replace('"', '""', $lead->email ?? '') . '"',
                                    '"' . str_replace('"', '""', $lead->phone ?? '') . '"',
                                    '"' . str_replace('"', '""', $lead->stage ?? '') . '"',
                                ]) . "\n";
                            }
                            return response()->streamDownload(function () use ($csv) {
                                echo $csv;
                            }, 'leads.csv');
                        }),
                    Action::make('export_excel')
                        ->label('Export Excel')
                        ->icon('heroicon-o-table-cells')
                        ->action(function () {
                            return Excel::download(
                                new LeadsExport(auth()->user()?->team_id),
                                'leads-' . now()->format('Ymd') . '.xlsx'
                            );
                        }),
                    Tables\Actions\BulkAction::make('reassign')
                        ->label('Reassign Selected')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Select::make('assigned_to_id')
                                ->label('Reassign To')
                                ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each->update(['assigned_to_id' => $data['assigned_to_id']]);
                        })
                        ->deselectRecordsAfterCompletion(),
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
                                    'owner_name' => $rowData['owner_name'] ?? $rowData['name'] ?? null,
                                    'email' => $rowData['email'] ?? null,
                                    'phone' => $rowData['phone'] ?? null,
                                    'lead_source' => $rowData['lead_source'] ?? $rowData['source'] ?? null,
                                    'stage' => $rowData['stage'] ?? 'new_lead',
                                    'team_id' => auth()->user()?->team_id,
                                ];
                                Lead::create($payload);
                                $imported++;
                            }
                            fclose($handle);
                            session()->flash('notification', "Imported {$imported} lead(s).");
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PropertiesRelationManager::class,
            TasksRelationManager::class,
            CallLogsRelationManager::class,
            SmsLogsRelationManager::class,
            AttachmentsRelationManager::class,
            ActivitiesRelationManager::class,
            EmailLogsRelationManager::class,
        ];
    }

   public static function getPages(): array
{
    return [
        'index' => Pages\ListLeads::route('/'),
        'create' => Pages\CreateLead::route('/create'),
        'view' => Pages\ViewLead::route('/{record}'),
        'edit' => Pages\EditLead::route('/{record}/edit'),
        // 'kanban' => Pages\LeadKanban::route('/kanban'), // e hoqëm, tani kemi page të veçantë
    ];
}

}
