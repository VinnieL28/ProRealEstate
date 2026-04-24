<?php

namespace App\Filament\Widgets;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Property;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        $teamId = auth()->user()?->team_id;
        $cacheKey = "dashboard_stats_team_" . ($teamId ?? 'all');

        $stats = Cache::remember($cacheKey, now()->addMinutes(2), function () use ($teamId) {
            $scope = fn ($q) => $teamId ? $q->where('team_id', $teamId) : $q;

            $lastWeekStart   = Carbon::now()->subDays(14)->startOfDay();
            $lastWeekEnd     = Carbon::now()->subDays(7)->endOfDay();
            $thisWeekStart   = Carbon::now()->subDays(7)->startOfDay();

            $totalLeads       = $scope(Lead::query())->count();
            $leadsThisWeek    = $scope(Lead::query())->whereBetween('created_at', [$thisWeekStart, now()])->count();
            $leadsLastWeek    = $scope(Lead::query())->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();

            $activeLeads      = $scope(Lead::query())->whereNotIn('stage', ['closed_won', 'closed_lost'])->count();
            $hotLeads         = $scope(Lead::query())->where('score', '>=', 70)->count();
            $totalProperties  = $scope(Property::query())->count();
            $openDeals        = $scope(Deal::query())->whereNotIn('stage', ['closed_won', 'closed_lost'])->count();
            $closedDeals      = $scope(Deal::query())->where('stage', 'closed_won')->count();
            $closedThisMonth  = $scope(Deal::query())->where('stage', 'closed_won')->whereMonth('updated_at', now()->month)->count();
            $revenueThisMonth = $scope(Deal::query())->where('stage', 'closed_won')->whereMonth('updated_at', now()->month)->sum('profit') ?? 0;
            $conversionRate   = $totalLeads > 0 ? round(($closedDeals / $totalLeads) * 100, 1) : 0;
            $avgProfit        = $scope(Deal::query())->where('stage', 'closed_won')->avg('profit') ?? 0;

            // Week-over-week deltas
            $leadsDelta = $leadsLastWeek > 0
                ? round((($leadsThisWeek - $leadsLastWeek) / $leadsLastWeek) * 100)
                : ($leadsThisWeek > 0 ? 100 : 0);

            // 7-day lead creation sparkline
            $leadChart = collect(range(6, 0))->map(function ($d) use ($scope) {
                return $scope(Lead::query())->whereDate('created_at', now()->subDays($d))->count();
            })->toArray();

            // 7-day deal close sparkline
            $dealChart = collect(range(6, 0))->map(function ($d) use ($scope) {
                return $scope(Deal::query())->where('stage', 'closed_won')->whereDate('updated_at', now()->subDays($d))->count();
            })->toArray();

            return compact(
                'totalLeads', 'activeLeads', 'hotLeads', 'totalProperties',
                'openDeals', 'closedDeals', 'closedThisMonth', 'revenueThisMonth',
                'conversionRate', 'avgProfit', 'leadsThisWeek', 'leadsDelta',
                'leadChart', 'dealChart'
            );
        });

        extract($stats);

        return [
            Stat::make('Total Leads', number_format($totalLeads))
                ->description($leadsDelta >= 0 ? "↑ {$leadsDelta}% vs last week" : "↓ " . abs($leadsDelta) . "% vs last week")
                ->descriptionIcon($leadsDelta >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->icon('heroicon-o-users')
                ->chart($leadChart)
                ->color($leadsDelta >= 0 ? 'success' : 'danger'),

            Stat::make('Hot Leads', number_format($hotLeads))
                ->description('Score ≥ 70 — ready to close')
                ->descriptionIcon('heroicon-m-fire')
                ->icon('heroicon-o-fire')
                ->color('danger'),

            Stat::make('Open Deals', number_format($openDeals))
                ->description($closedThisMonth . ' closed this month')
                ->descriptionIcon('heroicon-m-briefcase')
                ->icon('heroicon-o-briefcase')
                ->chart($dealChart)
                ->color('warning'),

            Stat::make('Revenue This Month', '$' . number_format((float) $revenueThisMonth))
                ->description('Profit from closed deals')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Conversion Rate', $conversionRate . '%')
                ->description('Leads → closed deals')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->icon('heroicon-o-chart-bar')
                ->color('primary'),

            Stat::make('Avg Deal Profit', '$' . number_format((float) $avgProfit))
                ->description($totalProperties . ' properties on file')
                ->descriptionIcon('heroicon-m-home')
                ->icon('heroicon-o-trophy')
                ->color('primary'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
