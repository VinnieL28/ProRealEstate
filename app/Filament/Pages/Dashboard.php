<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\DashboardStats;
use App\Filament\Widgets\TodayActivity;
use App\Filament\Widgets\ThisWeekStats;
use App\Filament\Widgets\LeadsByStageChart;
use App\Filament\Widgets\DealsPerMonthChart;
use App\Filament\Widgets\TasksDueWidget;
use App\Filament\Widgets\KpiPerformance;
use App\Filament\Widgets\LeaderboardWidget;
use App\Filament\Widgets\CalendarWidget;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function getWidgets(): array
    {
        return [
            DashboardStats::class,
            TodayActivity::class,
            ThisWeekStats::class,
            KpiPerformance::class,
            LeadsByStageChart::class,
            DealsPerMonthChart::class,
            TasksDueWidget::class,
            LeaderboardWidget::class,
            CalendarWidget::class,
        ];
    }
}
