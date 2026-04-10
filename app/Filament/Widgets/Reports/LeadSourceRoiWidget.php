<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Deal;
use App\Models\Lead;
use Filament\Widgets\Widget;

class LeadSourceRoiWidget extends Widget
{
    protected static string $view = 'filament.widgets.reports.lead-source-roi';

    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected $listeners = ['report-filter-changed' => 'loadData'];

    public array $rows = [];

    public function mount(): void { $this->loadData(); }

    public function loadData(): void
    {
        $teamId = auth()->user()?->team_id;
        $from   = session('report_date_from', now()->subYear()->startOfMonth()->toDateString());
        $until  = session('report_date_until', now()->endOfMonth()->toDateString());

        $sources = Lead::query()
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->whereBetween('created_at', [$from . ' 00:00:00', $until . ' 23:59:59'])
            ->whereNotNull('lead_source')
            ->selectRaw('lead_source, COUNT(*) as total')
            ->groupBy('lead_source')
            ->pluck('total', 'lead_source');

        $closed = Lead::query()
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->where('stage', 'closed_won')
            ->whereBetween('created_at', [$from . ' 00:00:00', $until . ' 23:59:59'])
            ->whereNotNull('lead_source')
            ->selectRaw('lead_source, COUNT(*) as total')
            ->groupBy('lead_source')
            ->pluck('total', 'lead_source');

        // Avg profit per closed deal per source (join through active_deal_id or deals.lead_id)
        $profitBySource = Deal::query()
            ->selectRaw('leads.lead_source, AVG(
                CASE
                    WHEN deals.profit IS NOT NULL THEN deals.profit
                    ELSE COALESCE(deals.sale_price, deals.assignment_fee, 0)
                         - (COALESCE(deals.purchase_price,0) + COALESCE(deals.closing_costs,0) + COALESCE(deals.marketing_costs,0))
                END
            ) as avg_profit')
            ->join('leads', 'deals.lead_id', '=', 'leads.id')
            ->when($teamId, fn ($q) => $q->where('deals.team_id', $teamId))
            ->where('deals.stage', 'closed_won')
            ->whereNotNull('leads.lead_source')
            ->groupBy('leads.lead_source')
            ->pluck('avg_profit', 'lead_source');

        $this->rows = $sources->map(function ($total, $source) use ($closed, $profitBySource, $from, $until) {
            $closedCount = $closed[$source] ?? 0;
            $conversion  = $total > 0 ? round(($closedCount / $total) * 100, 1) : 0;
            $avgProfit   = isset($profitBySource[$source]) ? round((float) $profitBySource[$source], 2) : null;

            return [
                'source'      => $source,
                'total'       => $total,
                'closed'      => $closedCount,
                'conversion'  => $conversion,
                'avg_profit'  => $avgProfit,
            ];
        })
        ->sortByDesc('conversion')
        ->values()
        ->toArray();
    }
}
