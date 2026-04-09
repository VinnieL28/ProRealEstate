<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class TasksDueWidget extends BaseWidget
{
    protected static ?string $heading = 'Tasks Due / Overdue';

    protected function getTableQuery(): Builder|Relation|null
    {
        return Task::query()
            ->whereNot('status', 'done')
            ->whereDate('due_date', '<=', Carbon::today())
            ->orderBy('due_date');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\TextColumn::make('assignedTo.name')->label('Assigned'),
            Tables\Columns\BadgeColumn::make('priority'),
            Tables\Columns\TextColumn::make('due_date')->dateTime(),
        ];
    }
}
