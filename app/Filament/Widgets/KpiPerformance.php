<?php

namespace App\Filament\Widgets;

use App\Models\Deal;
use App\Models\Lead;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KpiPerformance extends BaseWidget
{
    protected function getStats(): array
    {
        $totalLeads = Lead::count();
        $closedLeads = Lead::where('stage', 'closed_won')->count();
        $avgDaysToClose = Deal::whereNotNull('contract_date')
            ->whereNotNull('closing_date')
            ->where('stage', 'closed_won')
            ->get()
            ->avg(fn ($d) => Carbon::parse($d->contract_date)->diffInDays(Carbon::parse($d->closing_date))) ?? 0;

        $offersMade = Lead::where('stage', 'offer_made')->count();
        $contracts = Deal::where('stage', 'under_contract')->count();
        $appt = Lead::where('stage', 'appointment_set')->count();

        return [
            Stat::make('Lead → Appointment', $totalLeads > 0 ? round(($appt / $totalLeads) * 100, 2) . '%' : '0%'),
            Stat::make('Offer → Contract', $offersMade > 0 ? round(($contracts / $offersMade) * 100, 2) . '%' : '0%'),
            Stat::make('Lead → Close', $totalLeads > 0 ? round(($closedLeads / $totalLeads) * 100, 2) . '%' : '0%'),
            Stat::make('Avg Days to Close', round($avgDaysToClose, 1) . ' days'),
        ];
    }
}
