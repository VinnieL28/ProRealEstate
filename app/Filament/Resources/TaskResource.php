<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';

    protected static ?string $navigationGroup = 'Productivity';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')->required(),
                Textarea::make('description')->rows(3),
                DateTimePicker::make('due_date'),
                Select::make('assigned_to_id')->label('Assigned To')->options(fn () => User::orderBy('name')->whereNotNull('name')->pluck('name', 'id')->toArray())->searchable(),
                Select::make('related_type')->options([
                    'Lead' => 'Lead',
                    'Deal' => 'Deal',
                    'Property' => 'Property',
                ]),
                TextInput::make('related_id')->numeric(),
                Select::make('status')->options([
                    'open' => 'Open',
                    'in_progress' => 'In Progress',
                    'done' => 'Done',
                ])->default('open'),
                Select::make('priority')->options([
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                ])->default('medium'),
                Hidden::make('team_id')->default(fn () => auth()->user()?->team_id),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('assignedTo.name')->label('Assigned')->hiddenOn('sm'),
                Tables\Columns\BadgeColumn::make('status'),
                Tables\Columns\BadgeColumn::make('priority')->hiddenOn('sm'),
                Tables\Columns\TextColumn::make('due_date')->dateTime()->hiddenOn('sm'),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'open' => 'Open',
                    'in_progress' => 'In Progress',
                    'done' => 'Done',
                ]),
                SelectFilter::make('priority')->options([
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                ]),
                SelectFilter::make('assigned_to_id')->label('Assigned To')->options(fn () => User::orderBy('name')->whereNotNull('name')->pluck('name', 'id')->toArray()),
                Filter::make('due_today')
                    ->label('Due Today')
                    ->query(fn (Builder $query) => $query->whereDate('due_date', Carbon::today())),
                Filter::make('overdue')
                    ->label('Overdue')
                    ->query(fn (Builder $query) => $query->whereDate('due_date', '<', Carbon::today())->where('status', '!=', 'done')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('reassign')
                        ->label('Reassign Selected')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Select::make('assigned_to_id')
                                ->label('Reassign To')
                                ->options(fn () => User::orderBy('name')->whereNotNull('name')->pluck('name', 'id')->toArray())
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function ($records, array $data) {
                            $count = 0;
                            foreach ($records as $task) {
                                $task->update(['assigned_to_id' => $data['assigned_to_id']]);
                                $count++;
                            }
                            Notification::make()
                                ->title("Reassigned {$count} task(s) successfully.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('mark_done')
                        ->label('Mark as Done')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $task) {
                                $task->update(['status' => 'done']);
                                $count++;
                            }
                            Notification::make()
                                ->title("Marked {$count} task(s) as done.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
