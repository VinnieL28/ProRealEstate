<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use Filament\Widgets\Widget;

class AgentPerformanceWidget extends Widget
{
    protected static string $view = 'filament.widgets.reports.agent-performance';

    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    public array $agents = [];

    public function mount(): void
    {
        $teamId = auth()->user()?->team_id;

        $users = User::query()
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->get(['id', 'name', 'role']);

        $this->agents = $users->map(function (User $user) use ($teamId) {
            $leadsQuery = Lead::query()
                ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
                ->where('assigned_to_id', $user->id);

            $totalLeads  = (clone $leadsQuery)->count();
            $appts       = (clone $leadsQuery)->where('stage', 'appointment_set')->count();
            $closedWon   = (clone $leadsQuery)->where('stage', 'closed_won')->count();
            $closedLost  = (clone $leadsQuery)->where('stage', 'closed_lost')->count();

            $conversion  = $totalLeads > 0
                ? round(($closedWon / $totalLeads) * 100, 1)
                : 0;

            // Sum profit on deals tied to this agent's leads
            $totalProfit = Deal::query()
                ->join('leads', 'deals.lead_id', '=', 'leads.id')
                ->when($teamId, fn ($q) => $q->where('deals.team_id', $teamId))
                ->where('leads.assigned_to_id', $user->id)
                ->where('deals.stage', 'closed_won')
                ->sum(\DB::raw('
                    CASE
                        WHEN deals.profit IS NOT NULL THEN deals.profit
                        ELSE COALESCE(deals.sale_price, deals.assignment_fee, 0)
                             - (COALESCE(deals.purchase_price,0)
                                + COALESCE(deals.closing_costs,0)
                                + COALESCE(deals.marketing_costs,0))
                    END
                '));

            return [
                'name'         => $user->name,
                'role'         => $user->role,
                'total_leads'  => $totalLeads,
                'appointments' => $appts,
                'closed_won'   => $closedWon,
                'closed_lost'  => $closedLost,
                'conversion'   => $conversion,
                'total_profit' => round((float) $totalProfit, 2),
            ];
        })
        ->sortByDesc('total_profit')
        ->values()
        ->toArray();
    }
}
