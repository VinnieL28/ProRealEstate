<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Widgets\BarChartWidget;

class LeadsByStageChart extends BarChartWidget
{
    protected static ?string $heading = 'Leads by Stage';

    protected function getData(): array
    {
        $stages = [
            'new_lead' => 'New Lead',
            'no_contact' => 'No Contact Made',
            'contact_made' => 'Contact Made',
            'appointment_set' => 'Appointments Set',
            'due_diligence' => 'Due Diligence',
            'offer_made' => 'Offers Made',
            'under_contract' => 'Under Contract',
            'closed_won' => 'Closed Won',
            'closed_lost' => 'Closed Lost',
        ];

        $data = [];
        foreach ($stages as $stage => $label) {
            $data[] = Lead::where('stage', $stage)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Leads',
                    'data' => $data,
                    'backgroundColor' => '#22d3ee',
                ],
            ],
            'labels' => array_values($stages),
        ];
    }
}
