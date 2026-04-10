<?php

namespace App\Exports;

use App\Models\Lead;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeadsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(private ?int $teamId = null) {}

    public function collection(): Collection
    {
        return Lead::query()
            ->when($this->teamId, fn ($q) => $q->where('team_id', $this->teamId))
            ->with('assignedTo')
            ->orderByDesc('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Owner / Company', 'First Name', 'Last Name',
            'Phone', 'Email', 'Lead Source', 'Market',
            'Stage', 'Motivation', 'Hot Score',
            'Asking Price', 'Max Offer', 'Last Offer',
            'Assigned To', 'Last Touch', 'Created At',
        ];
    }

    public function map($lead): array
    {
        return [
            $lead->id,
            $lead->owner_name,
            $lead->first_name,
            $lead->last_name,
            $lead->primary_phone ?? $lead->phone,
            $lead->primary_email ?? $lead->email,
            $lead->lead_source,
            $lead->major_market,
            $lead->stage,
            $lead->motivation_level,
            number_format($lead->hot_score, 1),
            $lead->asking_price,
            $lead->max_offer,
            $lead->last_offer,
            $lead->assignedTo?->name,
            $lead->updated_at?->format('Y-m-d H:i'),
            $lead->created_at?->format('Y-m-d H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F59E0B']]],
        ];
    }
}
