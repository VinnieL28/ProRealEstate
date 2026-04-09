<?php

namespace App\Filament\Widgets;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Property;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalLeads = Lead::count();
        $activeLeads = Lead::whereNotIn('stage', ['closed_won', 'closed_lost'])->count();
        $totalProperties = Property::count();
        $openDeals = Deal::whereNotIn('stage', ['closed_won', 'closed_lost'])->count();
        $closedDeals = Deal::where('stage', 'closed_won')->count();
        $conversionRate = $totalLeads > 0 ? round(($closedDeals / $totalLeads) * 100, 2) : 0;
        $avgProfit = Deal::where('stage', 'closed_won')->avg('profit') ?? 0;

        return [
            Stat::make('Total Leads', $totalLeads)
                ->description('All leads in the system')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Active Leads', $activeLeads)
                ->description('Leads not yet closed')
                ->icon('heroicon-o-funnel')
                ->color('warning'),

            Stat::make('Total Properties', $totalProperties)
                ->description('Properties on record')
                ->icon('heroicon-o-home')
                ->color('info'),

            Stat::make('Open Deals', $openDeals)
                ->description('Deals in progress')
                ->icon('heroicon-o-briefcase')
                ->color('success'),

            Stat::make('Conversion Rate', $conversionRate . '%')
                ->description('Leads converted to closed deals')
                ->icon('heroicon-o-arrow-trending-up')
                ->color('success'),

            Stat::make('Avg Deal Profit', '$' . number_format($avgProfit, 2))
                ->description('Average profit on closed deals')
                ->icon('heroicon-o-currency-dollar')
                ->color('primary'),
        ];
    }
}
