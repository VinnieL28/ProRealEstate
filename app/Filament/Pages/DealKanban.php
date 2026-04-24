<?php

namespace App\Filament\Pages;

use App\Models\Activity;
use App\Models\Deal;
use App\Models\Task;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class DealKanban extends Page
{
    protected static ?string $navigationLabel = 'Deal Pipeline';
    protected static ?string $navigationGroup = 'Deals';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $slug = 'deal-kanban';
    protected static string $view = 'filament.pages.deal-kanban';
    protected static ?int $navigationSort = 5;

    /** @var array<string, array{label:string, deals:\Illuminate\Support\Collection}> */
    public array $columns = [];

    /** @var array<int, string> $cardNotes — temporary note buffer keyed by deal id */
    public array $cardNotes = [];

    public function mount(): void
    {
        $this->loadColumns();
    }

    protected function loadColumns(): void
    {
        $stages = [
            'analyzing'      => 'Analyzing',
            'contract_sent'  => 'Contract Sent',
            'under_contract' => 'Under Contract',
            'clear_to_close' => 'Clear to Close',
            'closed_won'     => 'Closed Won',
            'closed_lost'    => 'Closed Lost',
        ];

        $user   = auth()->user();
        $teamId = $user?->team_id;

        $query = Deal::query()
            ->with(['property', 'lead'])
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->orderByDesc('updated_at')
            ->get();

        $columns = [];
        foreach ($stages as $key => $label) {
            $deals = $query->where('stage', $key)->values();
            $columns[$key] = [
                'label'  => $label,
                'deals'  => $deals,
                'total'  => $deals->sum(fn ($d) => $d->sale_price ?? $d->assignment_fee ?? 0),
            ];
        }

        $this->columns = $columns;

        // Init cardNotes for any deal that doesn't have one
        foreach ($query as $deal) {
            if (!isset($this->cardNotes[$deal->id])) {
                $this->cardNotes[$deal->id] = '';
            }
        }
    }

    /**
     * Called by SortableJS via Livewire when a card is dragged to a new stage column.
     */
    public function moveCard(?int $dealId, string $newStage): void
    {
        $user = auth()->user();
        $deal = Deal::where('id', $dealId)
            ->when($user?->team_id, fn ($q) => $q->where('team_id', $user->team_id))
            ->first();

        if (!$deal) {
            return;
        }

        $deal->update(['stage' => $newStage]);

        $this->loadColumns();

        Notification::make()
            ->title('Deal moved to ' . ucwords(str_replace('_', ' ', $newStage)))
            ->success()
            ->send();
    }

    /**
     * Save a quick note from a kanban card (creates an Activity).
     */
    public function saveNote(int $dealId): void
    {
        $note = trim($this->cardNotes[$dealId] ?? '');

        if (!$note) {
            return;
        }

        $user = auth()->user();
        $deal = Deal::find($dealId);
        if (!$deal) {
            return;
        }

        Activity::create([
            'team_id'      => $deal->team_id,
            'user_id'      => $user?->id,
            'related_type' => 'Deal',
            'related_id'   => $deal->id,
            'type'         => 'note',
            'description'  => $note,
        ]);

        $this->cardNotes[$dealId] = '';

        Notification::make()->title('Note saved on deal')->success()->send();
    }
}
