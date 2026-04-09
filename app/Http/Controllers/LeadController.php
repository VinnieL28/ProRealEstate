<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Support\LeadPipeline;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LeadController extends Controller
{
    public function index()
    {
        return redirect()->to('/admin/leads');
    }

    public function active()
    {
        $board = LeadPipeline::build(fn ($query) => $query->whereNotIn('stage', ['closed_won', 'closed_lost']));

        return view('leads.board', [
            'pageTitle' => 'Active Leads',
            'board' => $board,
            'totalLeads' => Lead::count(),
            'filters' => $this->pipelineFilters(),
            'context' => 'active',
        ]);
    }

    public function warm()
    {
        $warmStages = LeadPipeline::warmStages();
        $leads = Lead::query()
            ->whereIn('stage', $warmStages)
            ->latest('updated_at')
            ->get()
            ->map(fn (Lead $lead) => LeadPipeline::present($lead));

        $columns = [
            'Seller Name' => 'name',
            'Property Address' => 'property_address',
            'Status' => 'status',
            'Beds | Baths' => 'beds_baths',
            'Phone' => 'phone',
            'Email' => 'email',
            'Lead Source' => 'source',
            'Campaign Name' => 'campaign_name',
            'Team Assigned' => 'team_assigned',
            'Pending Tasks' => 'pending_tasks',
            'Tags' => 'tags',
            'Communications' => 'communications',
            'Last Outgoing Touch' => 'last_outgoing_touch',
            'Last Incoming Touch' => 'last_incoming_touch',
            'Last Offer Info' => 'last_offer_info',
            'UC Date' => 'uc_date',
            'UC Price' => 'uc_price',
            'Sch Closing Date' => 'sch_closing_date',
            'Date' => 'created',
        ];

        return view('leads.warm', [
            'pageTitle' => 'Warm Leads',
            'totalLeads' => $leads->count(),
            'filters' => $this->pipelineFilters(),
            'columns' => $columns,
            'leads' => $leads,
        ]);
    }

    public function create()
    {
        return redirect()->to('/admin/leads/create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'owner_name' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:255',
            'stage' => 'nullable|string|max:50',
            'lead_source' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $payload = [
            'owner_name' => $data['owner_name'] ?? $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'stage' => $data['stage'] ?? 'new_lead',
            'lead_source' => $data['lead_source'] ?? $data['source'] ?? null,
            'notes' => $data['notes'] ?? null,
            'team_id' => auth()->user()?->team_id,
        ];

        Lead::create($payload);
        return redirect()->route('leads.index')->with('ok', 'Lead created.');
    }

    public function edit(Lead $lead)
    {
        return redirect()->to('/admin/leads/'.$lead->id.'/edit');
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'owner_name' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:255',
            'stage' => 'nullable|string|max:50',
            'lead_source' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $lead->update([
            'owner_name' => $data['owner_name'] ?? $data['name'] ?? $lead->owner_name,
            'email' => $data['email'] ?? $lead->email,
            'phone' => $data['phone'] ?? $lead->phone,
            'stage' => $data['stage'] ?? $lead->stage,
            'lead_source' => $data['lead_source'] ?? $data['source'] ?? $lead->lead_source,
            'notes' => $data['notes'] ?? $lead->notes,
        ]);
        return redirect()->route('leads.index')->with('ok', 'Lead updated.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return back()->with('ok', 'Lead deleted.');
    }

    public function import(Request $request)
    {
        $request->validate(['csv' => 'required|file|mimes:csv,txt']);
        $file = $request->file('csv');
        $imported = 0; $skipped = 0;
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $headers = null;
            while (($row = fgetcsv($handle)) !== false) {
                if ($headers === null) { $headers = array_map(fn($h) => Str::of($h)->lower()->toString(), $row); continue; }
                $data = array_combine($headers, $row);
                if (!$data) { $skipped++; continue; }

                $payload = [
                    'name' => $data['name'] ?? ($data['full_name'] ?? null),
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? ($data['phone_number'] ?? null),
                    'status' => $data['status'] ?? null,
                    'source' => $data['source'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ];
                if (!empty($payload['name'])) { Lead::create($payload); $imported++; } else { $skipped++; }
            }
            fclose($handle);
        }
        return back()->with('ok', "Imported $imported lead(s). Skipped $skipped.");
    }

    protected function pipelineFilters(): array
    {
        return [
            ['label' => 'Follow up', 'count' => 0],
            ['label' => 'Incomplete Info', 'count' => 0],
            ['label' => 'Seller Appts', 'count' => 0],
            ['label' => 'Offers', 'count' => 0],
            ['label' => 'Contracts', 'count' => 0],
            ['label' => 'Call Duration', 'count' => 0],
        ];
    }
}
