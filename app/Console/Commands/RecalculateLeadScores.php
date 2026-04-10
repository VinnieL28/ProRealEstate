<?php

namespace App\Console\Commands;

use App\Models\Lead;
use Illuminate\Console\Command;

class RecalculateLeadScores extends Command
{
    protected $signature   = 'leads:recalculate-scores {--team= : Only recalculate for a specific team ID}';
    protected $description = 'Recalculate the 1-100 lead score for all active leads and persist to the score column.';

    public function handle(): int
    {
        $teamId = $this->option('team') ? (int) $this->option('team') : null;

        $query = Lead::query()
            ->whereNotIn('stage', ['closed_won', 'closed_lost'])
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId));

        $total   = $query->count();
        $updated = 0;

        $this->withProgressBar($query->cursor(), function (Lead $lead) use (&$updated) {
            $score = $lead->calculateScore();
            if ($lead->score !== $score) {
                $lead->updateQuietly(['score' => $score]);
                $updated++;
            }
        });

        $this->newLine();
        $this->info("Processed {$total} leads. Updated scores on {$updated}.");

        return self::SUCCESS;
    }
}
