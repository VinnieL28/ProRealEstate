<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TasksDueWidget extends BaseWidget
{
    protected static ?string $heading = 'Tasks Due / Overdue';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::query()
                    ->whereNot('status', 'done')
                    ->whereDate('due_date', '<=', Carbon::today())
                    ->orderBy('due_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('assignedTo.name')->label('Assigned'),
                Tables\Columns\BadgeColumn::make('priority'),
                Tables\Columns\TextColumn::make('due_date')->dateTime(),
            ]);
    }
}
