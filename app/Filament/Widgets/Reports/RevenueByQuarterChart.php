<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Deal;
use Filament\Widgets\BarChartWidget;
use Illuminate\Support\Carbon;

class RevenueByQuarterChart extends BarChartWidget
{
    protected static ?string $heading = 'Revenue by Quarter — Last 8 Quarters';

    protected int | string | array $columnSpan = 'half';

    protected static bool $isDiscovered = false;

    protected function getData(): array
    {
        $teamId  = auth()->user()?->team_id;
        $labels  = [];
        $profit  = [];

        // Build 8 quarters back from current
        $now     = Carbon::now();
        $current = Carbon::create($now->year, (int) ceil($now->month / 3) * 3 - 2, 1);

        for ($i = 7; $i >= 0; $i--) {
            $qStart = $current->copy()->subMonths($i * 3)->startOfMonth();
            $qEnd   = $qStart->copy()->addMonths(3)->subDay()->endOfDay();
            $qLabel = 'Q' . ceil($qStart->month / 3) . ' ' . $qStart->year;

            $labels[] = $qLabel;

            $deals = Deal::query()
                ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
                ->where('stage', 'closed_won')
                ->whereBetween('closing_date', [$qStart, $qEnd])
                ->get();

            $profit[] = round((float) $deals->sum(fn ($d) => $d->profit ?? 0), 2);
        }

        return [
            'datasets' => [[
                'label'           => 'Profit ($)',
                'data'            => $profit,
                'backgroundColor' => '#10b981',
            ]],
            'labels' => $labels,
        ];
    }
}
