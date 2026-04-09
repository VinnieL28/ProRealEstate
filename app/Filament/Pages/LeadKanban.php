<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Lead; // ❗ nëse modeli yt quhet ndryshe (p.sh. SellerLead), ndryshoje këtu

class LeadKanban extends Page
{
    protected static ?string $navigationLabel = 'Lead Kanban';
    protected static ?string $navigationGroup = 'Leads';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    // URL-ja: /admin/lead-kanban
    protected static ?string $slug = 'lead-kanban';

    protected static string $view = 'filament.pages.lead-kanban';

    protected static ?int $navigationSort = 20;

    /** @var array<string, array{label:string, leads:\Illuminate\Support\Collection}> */
    public array $columns = [];

    public function mount(): void
    {
        // definimi i stage-ve
        $stages = [
            'new_lead'        => 'New',
            'contacted'       => 'Contacted',
            'follow_up'       => 'Follow Up',
            'appointment_set' => 'Appointment',
            'offer_made'      => 'Offer',
            'under_contract'  => 'Contract',
            'closed_won'      => 'Closed Won',
            'closed_lost'     => 'Closed Lost',
        ];

        $columns = [];

        foreach ($stages as $key => $label) {
            $columns[$key] = [
                'label' => $label,
                'leads' => Lead::query()   // ❗ ndrysho në SellerLead::query() nëse duhet
                    ->where('stage', $key) // ❗ nëse kolona quhet ndryshe (p.sh. status), ndryshoje
                    ->orderByDesc('created_at')
                    ->get(),
            ];
        }

        $this->columns = $columns;
    }
}
