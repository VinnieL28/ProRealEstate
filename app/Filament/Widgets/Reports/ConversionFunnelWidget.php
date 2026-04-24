<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Lead;
use Filament\Widgets\Widget;

class ConversionFunnelWidget extends Widget
{
    protected static string $view = 'filament.widgets.reports.conversion-funnel';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function getViewData(): array
    {
        $teamId = auth()->user()?->team_id;
        $scope = fn ($q) => $teamId ? $q->where('team_id', $teamId) : $q;

        $stages = [
            'new_lead'         => ['label' => 'New Leads',         'color' => '#60a5fa'],
            'contact_made'     => ['label' => 'Contact Made',      'color' => '#38bdf8'],
            'appointment_set'  => ['label' => 'Appointments',      'color' => '#a78bfa'],
            'offer_made'       => ['label' => 'Offers Made',       'color' => '#f59e0b'],
            'under_contract'   => ['label' => 'Under Contract',    'color' => '#f97316'],
            'closed_won'       => ['label' => 'Closed Won',        'color' => '#22c55e'],
        ];

        // Cumulative counts — anyone who ever reached this stage or beyond
        $stageOrder = array_keys($stages);
        $total = $scope(Lead::query())->count();

        $data = [];
        $previousCount = $total;
        $maxCount = $total ?: 1;

        foreach ($stageOrder as $i => $key) {
            $reachedOrBeyond = $scope(Lead::query())
                ->whereIn('stage', array_slice($stageOrder, $i))
                ->count();

            $percent = $maxCount > 0 ? round(($reachedOrBeyond / $maxCount) * 100, 1) : 0;
            $dropOff = $previousCount > 0 ? round((($previousCount - $reachedOrBeyond) / $previousCount) * 100, 1) : 0;

            $data[] = [
                'label'   => $stages[$key]['label'],
                'color'   => $stages[$key]['color'],
                'count'   => $reachedOrBeyond,
                'percent' => $percent,
                'drop'    => $i > 0 ? $dropOff : null,
                'width'   => max($percent, 5),
            ];

            $previousCount = $reachedOrBeyond;
        }

        return ['data' => $data, 'total' => $total];
    }
}
