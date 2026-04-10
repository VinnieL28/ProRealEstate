<?php

namespace App\Exports;

use App\Models\Deal;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DealsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(private ?int $teamId = null) {}

    public function collection(): Collection
    {
        return Deal::query()
            ->when($this->teamId, fn ($q) => $q->where('team_id', $this->teamId))
            ->with(['lead', 'property'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Deal Name', 'Property', 'Seller', 'Contract Type',
            'Stage', 'Contract Date', 'Closing Date',
            'Purchase Price', 'Assignment Fee', 'Sale Price',
            'Closing Costs', 'Marketing Costs', 'Profit', 'ROI %',
        ];
    }

    public function map($deal): array
    {
        return [
            $deal->id,
            $deal->name,
            $deal->property?->address,
            $deal->lead?->owner_name,
            $deal->contract_type,
            $deal->stage,
            $deal->contract_date?->format('Y-m-d'),
            $deal->closing_date?->format('Y-m-d'),
            $deal->purchase_price,
            $deal->assignment_fee,
            $deal->sale_price,
            $deal->closing_costs,
            $deal->marketing_costs,
            $deal->profit,
            $deal->roi,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F59E0B']]],
        ];
    }
}
