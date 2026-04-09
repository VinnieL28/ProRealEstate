<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Deal;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Carbon;

class RevenueByMonthChart extends LineChartWidget
{
    protected static ?string $heading = 'Revenue (Profit) by Month — Last 12 Months';

    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected function getData(): array
    {
        $teamId = auth()->user()?->team_id;
        $labels  = [];
        $profit  = [];
        $count   = [];

        for ($i = 11; $i >= 0; $i--) {
            $month    = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $deals = Deal::query()
                ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
                ->where('stage', 'closed_won')
                ->whereYear('closing_date', $month->year)
                ->whereMonth('closing_date', $month->month)
                ->get();

            $profit[] = round((float) $deals->sum(fn ($d) => $d->profit ?? 0), 2);
            $count[]  = $deals->count();
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Profit ($)',
                    'data'            => $profit,
                    'borderColor'     => '#f59e0b',
                    'backgroundColor' => 'rgba(245,158,11,0.12)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => 'Deals Closed',
                    'data'            => $count,
                    'borderColor'     => '#6366f1',
                    'backgroundColor' => 'rgba(99,102,241,0.12)',
                    'fill'            => false,
                    'tension'         => 0.4,
                    'yAxisID'         => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y'  => ['position' => 'left',  'title' => ['display' => true, 'text' => 'Profit ($)']],
                'y1' => ['position' => 'right', 'grid'  => ['drawOnChartArea' => false], 'title' => ['display' => true, 'text' => 'Deals']],
            ],
        ];
    }
}
