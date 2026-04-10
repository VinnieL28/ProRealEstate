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

    protected $listeners = ['report-filter-changed' => '$refresh'];

    protected function getData(): array
    {
        $teamId  = auth()->user()?->team_id;
        $from    = Carbon::parse(session('report_date_from', now()->subMonths(23)->startOfMonth()));
        $until   = Carbon::parse(session('report_date_until', now()->endOfMonth()));
        $labels  = [];
        $profit  = [];

        // Walk quarters within the selected date range (max 12)
        $qStart = $from->copy()->startOfQuarter();
        $count  = 0;
        while ($qStart->lte($until) && $count < 12) {
            $qEnd   = $qStart->copy()->endOfQuarter();
            $qLabel = 'Q' . ceil($qStart->month / 3) . ' ' . $qStart->year;

            $labels[] = $qLabel;

            $deals = Deal::query()
                ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
                ->where('stage', 'closed_won')
                ->whereBetween('closing_date', [$qStart, $qEnd])
                ->get();

            $profit[] = round((float) $deals->sum(fn ($d) => $d->profit ?? 0), 2);
            $qStart->addQuarter();
            $count++;
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
