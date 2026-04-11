<?php

namespace App\Filament\Widgets;

use App\Models\CallLog;
use App\Models\Lead;
use App\Models\Task;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodayActivity extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();
        $newLeads = Lead::whereDate('created_at', $today)->count();
        $calls = CallLog::whereDate('called_at', $today)->count();
        $appointments = Lead::whereDate('updated_at', $today)->where('stage', 'appointment_set')->count();
        $tasksDue = Task::whereDate('due_date', $today)->where('status', '!=', 'done')->count();

        return [
            Stat::make('New Leads Today', $newLeads),
            Stat::make('Calls Logged Today', $calls),
            Stat::make('Appointments Set Today', $appointments),
            Stat::make('Tasks Due Today', $tasksDue),
        ];
    }
}
