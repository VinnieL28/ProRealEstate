<?php

namespace App\Console\Commands;

use App\Mail\DailyDigest;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendDailyDigest extends Command
{
    protected $signature = 'notifications:send-daily-digest {--user= : Send only to a specific user ID}';

    protected $description = 'Send daily CRM digest email to all active users';

    public function handle(): int
    {
        $query = User::query()->whereNotNull('email');

        if ($userId = $this->option('user')) {
            $query->where('id', $userId);
        }

        $users = $query->get();
        $sent  = 0;
        $today = Carbon::today();

        $this->info("Sending daily digest to {$users->count()} user(s)...");
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            try {
                $digest = $this->buildDigest($user, $today);
                Mail::to($user->email)->send(new DailyDigest($user, $digest));
                $sent++;
            } catch (\Throwable $e) {
                $this->newLine();
                $this->warn("Failed for user {$user->id} ({$user->email}): " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. Sent {$sent}/{$users->count()} digest emails.");

        return self::SUCCESS;
    }

    private function buildDigest(User $user, Carbon $today): array
    {
        $yesterday = $today->copy()->subDay();

        // My open leads
        $myOpenLeads = Lead::where('assigned_to_id', $user->id)
            ->whereNotIn('stage', ['closed_won', 'closed_lost', 'dead'])
            ->count();

        // New leads assigned to me yesterday
        $newLeadsYesterday = Lead::where('assigned_to_id', $user->id)
            ->whereDate('created_at', $yesterday)
            ->count();

        // Tasks due today
        $tasksDueToday = Task::where('assigned_to_id', $user->id)
            ->whereNotIn('status', ['done'])
            ->whereDate('due_date', $today)
            ->with('relatedLead')
            ->orderBy('priority', 'desc')
            ->limit(10)
            ->get();

        // Overdue tasks
        $overdueTasks = Task::where('assigned_to_id', $user->id)
            ->whereNotIn('status', ['done'])
            ->where('due_date', '<', $today)
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Hot leads (score >= 70) assigned to me
        $hotLeads = Lead::where('assigned_to_id', $user->id)
            ->where('score', '>=', 70)
            ->whereNotIn('stage', ['closed_won', 'closed_lost', 'dead'])
            ->orderByDesc('score')
            ->limit(5)
            ->get();

        return [
            'my_open_leads'        => $myOpenLeads,
            'new_leads_yesterday'  => $newLeadsYesterday,
            'tasks_due_today'      => $tasksDueToday->count(),
            'tasks_overdue'        => $overdueTasks->count(),

            'tasks_due_today_list' => $tasksDueToday->map(fn (Task $t) => [
                'title'    => $t->title,
                'priority' => $t->priority ?? 'medium',
                'related'  => $t->related_type . ' #' . $t->related_id,
            ])->toArray(),

            'overdue_tasks_list' => $overdueTasks->map(fn (Task $t) => [
                'title'    => $t->title,
                'due_date' => Carbon::parse($t->due_date)->format('M j'),
                'priority' => $t->priority ?? 'medium',
            ])->toArray(),

            'hot_leads' => $hotLeads->map(fn (Lead $l) => [
                'name'  => trim(($l->first_name ?? '') . ' ' . ($l->last_name ?? '')) ?: ($l->owner_name ?? 'Unknown'),
                'score' => $l->score,
                'stage' => ucwords(str_replace('_', ' ', $l->stage ?? '')),
                'phone' => $l->phone ?? '—',
            ])->toArray(),
        ];
    }
}
