<?php

namespace App\Filament\Widgets;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Property;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class TopStats extends BaseWidget
{
    protected function getCards(): array
    {
        $totalLeads = Lead::count();
        $activeLeads = Lead::whereNotIn('stage', ['closed_won', 'closed_lost'])->count();
        $totalProperties = Property::count();
        $openDeals = Deal::whereNotIn('stage', ['closed_won', 'closed_lost'])->count();
        $closedDeals = Deal::where('stage', 'closed_won')->count();
        $conversion = $totalLeads > 0 ? round(($closedDeals / $totalLeads) * 100, 2) : 0;
        $avgProfit = Deal::where('stage', 'closed_won')->get()->avg(fn ($deal) => $deal->profit) ?? 0;

        return [
            Card::make('Total Leads', $totalLeads),
            Card::make('Active Leads', $activeLeads),
            Card::make('Total Properties', $totalProperties),
            Card::make('Open Deals', $openDeals),
            Card::make('Conversion Rate', $conversion . '%'),
            Card::make('Avg Deal Profit', '$' . number_format($avgProfit, 2)),
        ];
    }
}
