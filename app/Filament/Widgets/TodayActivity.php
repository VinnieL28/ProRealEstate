<?php

namespace App\Filament\Widgets;

use App\Models\CallLog;
use App\Models\Lead;
use App\Models\Task;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class TodayActivity extends BaseWidget
{
    protected function getCards(): array
    {
        $today = Carbon::today();
        $newLeads = Lead::whereDate('created_at', $today)->count();
        $calls = CallLog::whereDate('called_at', $today)->count();
        $appointments = Lead::whereDate('updated_at', $today)->where('stage', 'appointment_set')->count();
        $tasksDue = Task::whereDate('due_date', $today)->where('status', '!=', 'done')->count();

        return [
            Card::make('New Leads Today', $newLeads),
            Card::make('Calls Logged Today', $calls),
            Card::make('Appointments Set Today', $appointments),
            Card::make('Tasks Due Today', $tasksDue),
        ];
    }
}
