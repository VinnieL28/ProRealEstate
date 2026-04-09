<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Deal;
use App\Models\User;
use Filament\Widgets\BarChartWidget;

class RevenueByAgentChart extends BarChartWidget
{
    protected static ?string $heading = 'Revenue by Agent (Closed Deals)';

    protected int | string | array $columnSpan = 'half';

    protected static bool $isDiscovered = false;

    protected function getData(): array
    {
        $teamId = auth()->user()?->team_id;

        // Join deals → leads → users to group profit by the lead's assigned agent
        $rows = Deal::query()
            ->selectRaw('leads.assigned_to_id, SUM(
                CASE
                    WHEN deals.profit IS NOT NULL THEN deals.profit
                    ELSE COALESCE(deals.sale_price, deals.assignment_fee, 0)
                         - (COALESCE(deals.purchase_price,0) + COALESCE(deals.closing_costs,0) + COALESCE(deals.marketing_costs,0))
                END
            ) as total_profit, COUNT(deals.id) as deal_count')
            ->join('leads', 'deals.lead_id', '=', 'leads.id')
            ->when($teamId, fn ($q) => $q->where('deals.team_id', $teamId))
            ->where('deals.stage', 'closed_won')
            ->whereNotNull('leads.assigned_to_id')
            ->groupBy('leads.assigned_to_id')
            ->orderByDesc('total_profit')
            ->limit(10)
            ->get();

        $userIds = $rows->pluck('assigned_to_id')->filter()->toArray();
        $users   = User::whereIn('id', $userIds)->pluck('name', 'id');

        $labels = $rows->map(fn ($r) => $users[$r->assigned_to_id] ?? 'Unknown')->toArray();
        $data   = $rows->map(fn ($r) => round((float) $r->total_profit, 2))->toArray();

        return [
            'datasets' => [[
                'label'           => 'Total Profit ($)',
                'data'            => $data,
                'backgroundColor' => '#8b5cf6',
            ]],
            'labels' => $labels,
        ];
    }
}
