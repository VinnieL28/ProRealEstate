<?php

namespace App\Imports;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class LeadsImport implements ToCollection, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    public int $imported = 0;
    public int $skipped  = 0;
    public array $failedRows = [];

    private int $teamId;

    public function __construct(int $teamId)
    {
        $this->teamId = $teamId;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +2 because row 1 is header

            // Normalize keys: lowercase + underscores
            $r = collect($row)->mapWithKeys(
                fn ($v, $k) => [Str::snake(strtolower(trim($k))) => trim((string) $v)]
            );

            $name  = $r->get('name')        ?? $r->get('owner_name')   ?? null;
            $phone = $r->get('phone')        ?? $r->get('phone_number') ?? null;
            $email = $r->get('email')        ?? null;

            // Require at least a name
            if (empty($name)) {
                $this->failedRows[] = [
                    'row'    => $rowNum,
                    'reason' => 'Missing required field: name',
                ];
                continue;
            }

            // Deduplication: match on phone OR email within the same team
            $duplicate = false;
            if ($phone) {
                $duplicate = Lead::where('team_id', $this->teamId)
                    ->where('phone', $phone)
                    ->exists();
            }
            if (!$duplicate && $email) {
                $duplicate = Lead::where('team_id', $this->teamId)
                    ->where('email', $email)
                    ->exists();
            }

            if ($duplicate) {
                $this->skipped++;
                continue;
            }

            // Validate stage
            $validStages = [
                'new_lead', 'contacted', 'qualified', 'follow_up',
                'appointment_set', 'negotiation', 'under_contract',
                'closed_won', 'closed_lost',
            ];
            $stage = $r->get('stage') ?: 'new_lead';
            if (!in_array($stage, $validStages, true)) {
                $stage = 'new_lead';
            }

            // Resolve assigned agent by name or email if provided
            $assignedToId = null;
            $agentField = $r->get('assigned_to') ?? $r->get('agent') ?? null;
            if ($agentField) {
                $agent = User::where('team_id', $this->teamId)
                    ->where(function ($q) use ($agentField) {
                        $q->where('name', $agentField)->orWhere('email', $agentField);
                    })->first();
                $assignedToId = $agent?->id;
            }

            Lead::create([
                'owner_name'        => $name,
                'email'             => $email ?: null,
                'phone'             => $phone ?: null,
                'lead_source'       => $r->get('lead_source') ?? $r->get('source') ?? null,
                'stage'             => $stage,
                'motivation'        => $r->get('motivation') ? (int) $r->get('motivation') : null,
                'property_type'     => $r->get('property_type') ?? null,
                'suburb'            => $r->get('suburb') ?? $r->get('city') ?? null,
                'notes'             => $r->get('notes') ?? null,
                'assigned_to_id'    => $assignedToId,
                'team_id'           => $this->teamId,
            ]);

            $this->imported++;
        }
    }
}
