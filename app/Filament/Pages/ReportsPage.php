<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Reports\AgentPerformanceWidget;
use App\Filament\Widgets\Reports\LeadSourceRoiWidget;
use App\Filament\Widgets\Reports\PipelineVelocityWidget;
use App\Filament\Widgets\Reports\RevenueByAgentChart;
use App\Filament\Widgets\Reports\ReportFilterWidget;
use App\Filament\Widgets\Reports\RevenueByMonthChart;
use App\Filament\Widgets\Reports\RevenueByQuarterChart;
use Filament\Pages\Page;

class ReportsPage extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Reporting';
    protected static ?string $navigationLabel = 'Analytics';
    protected static ?string $slug            = 'reports';
    protected static ?string $title           = 'Reporting & Analytics';
    protected static string  $view            = 'filament.pages.reports';
    protected static ?int    $navigationSort  = 1;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['owner', 'admin', 'acquisition_manager', 'dispo_manager'], true);
    }

    public function getWidgets(): array
    {
        return [
            ReportFilterWidget::class,
            RevenueByMonthChart::class,
            RevenueByQuarterChart::class,
            RevenueByAgentChart::class,
            LeadSourceRoiWidget::class,
            PipelineVelocityWidget::class,
            AgentPerformanceWidget::class,
        ];
    }
}
