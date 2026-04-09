<?php

namespace App\Filament\Widgets;

use App\Models\Deal;
use Filament\Widgets\BarChartWidget;
use Illuminate\Support\Carbon;

class DealsPerMonthChart extends BarChartWidget
{
    protected static ?string $heading = 'Deals Closed per Month';

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');
            $data[] = Deal::where('stage', 'closed_won')
                ->whereYear('closing_date', $month->year)
                ->whereMonth('closing_date', $month->month)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Closed Won',
                    'data' => $data,
                    'backgroundColor' => '#a855f7',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
