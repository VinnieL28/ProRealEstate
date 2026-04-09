<?php

namespace App\Filament\Widgets;

use App\Models\Deal;
use App\Models\Lead;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class ThisWeekStats extends BaseWidget
{
    protected function getCards(): array
    {
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        $newLeads = Lead::whereBetween('created_at', [$start, $end])->count();
        $appointments = Lead::whereBetween('updated_at', [$start, $end])->where('stage', 'appointment_set')->count();
        $offers = Lead::whereBetween('updated_at', [$start, $end])->where('stage', 'offer_made')->count();
        $contracts = Deal::whereBetween('updated_at', [$start, $end])->where('stage', 'closed_won')->count();

        return [
            Card::make('New Leads This Week', $newLeads),
            Card::make('Appointments This Week', $appointments),
            Card::make('Offers Made This Week', $offers),
            Card::make('Contracts Secured This Week', $contracts),
        ];
    }
}
