<?php

namespace App\Filament\Pages;

use App\Models\Lead;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class DuplicateLeadsPage extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-document-duplicate';
    protected static ?string $navigationGroup = 'Leads';
    protected static ?string $navigationLabel = 'Find Duplicates';
    protected static ?string $slug            = 'duplicate-leads';
    protected static ?string $title           = 'Find & Merge Duplicate Leads';
    protected static string  $view            = 'filament.pages.duplicate-leads';
    protected static ?int    $navigationSort  = 21;

    public array $duplicates = [];
    public string $matchBy = 'phone';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function mount(): void
    {
        $this->scan();
    }

    public function scan(): void
    {
        $teamId = auth()->user()?->team_id;
        $column = $this->matchBy === 'email' ? 'email' : 'phone';

        $query = Lead::query()
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->whereNotNull($column)
            ->where($column, '!=', '');

        // Group by normalized value
        $leads = $query->get();

        $groups = $leads->groupBy(function ($lead) use ($column) {
            $val = (string) $lead->{$column};
            return strtolower(preg_replace('/[^a-z0-9@]/i', '', $val));
        })->filter(fn ($group) => $group->count() > 1);

        $this->duplicates = $groups->map(function ($group, $key) use ($column) {
            return [
                'key'   => $key,
                'value' => $group->first()->{$column},
                'leads' => $group->map(fn ($l) => [
                    'id'          => $l->id,
                    'name'        => $l->owner_name ?: trim(($l->first_name ?? '') . ' ' . ($l->last_name ?? '')) ?: 'Lead #' . $l->id,
                    'phone'       => $l->phone ?? '—',
                    'email'       => $l->email ?? '—',
                    'stage'       => $l->stage,
                    'created_at'  => $l->created_at?->format('M d, Y'),
                    'score'       => $l->score,
                    'edit_url'    => route('filament.admin.resources.leads.edit', $l),
                ])->values()->toArray(),
            ];
        })->values()->toArray();
    }

    public function changeMatchBy(string $by): void
    {
        $this->matchBy = in_array($by, ['phone', 'email'], true) ? $by : 'phone';
        $this->scan();
    }

    public function mergeKeepFirst(array $leadIds): void
    {
        if (count($leadIds) < 2) return;

        $keepId = $leadIds[0];
        $deleteIds = array_slice($leadIds, 1);

        Lead::whereIn('id', $deleteIds)->delete();

        Notification::make()
            ->title("Merged " . count($leadIds) . " leads — kept #{$keepId}, deleted " . count($deleteIds))
            ->success()
            ->send();

        $this->scan();
    }
}
