<?php

namespace App\Console\Commands;

use App\Models\DripEnrollment;
use App\Models\DripStep;
use App\Models\EmailLog;
use App\Models\SmsLog;
use App\Services\TwilioService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessDripEnrollments extends Command
{
    protected $signature = 'drip:process {--limit=200 : Max enrollments to process per run}';
    protected $description = 'Send the next due drip step for each active enrollment.';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $due = DripEnrollment::query()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('next_send_at')->orWhere('next_send_at', '<=', now());
            })
            ->with(['campaign.steps' => fn ($q) => $q->orderBy('step_order'), 'lead'])
            ->limit($limit)
            ->get();

        if ($due->isEmpty()) {
            $this->info('No drip enrollments due.');
            return self::SUCCESS;
        }

        $sent = 0;
        $completed = 0;
        $skipped = 0;

        foreach ($due as $enrollment) {
            try {
                $campaign = $enrollment->campaign;
                if (!$campaign || !$campaign->is_active) {
                    $enrollment->update(['status' => 'paused']);
                    $skipped++;
                    continue;
                }

                $steps = $campaign->steps;
                $nextOrder = ($enrollment->current_step ?? 0) + 1;
                $step = $steps->firstWhere('step_order', $nextOrder);

                if (!$step) {
                    $enrollment->update([
                        'status'       => 'completed',
                        'next_send_at' => null,
                    ]);
                    $completed++;
                    continue;
                }

                $this->sendStep($step, $enrollment);

                $followingStep = $steps->firstWhere('step_order', $nextOrder + 1);
                $enrollment->update([
                    'current_step' => $nextOrder,
                    'next_send_at' => $followingStep
                        ? now()->addDays((int) ($followingStep->delay_days ?? 1))
                        : null,
                    'status' => $followingStep ? 'active' : 'completed',
                ]);

                $sent++;
            } catch (\Throwable $e) {
                Log::error("Drip enrollment {$enrollment->id} failed: " . $e->getMessage());
                $skipped++;
            }
        }

        $this->info("Drip run complete. Sent: {$sent}, Completed: {$completed}, Skipped: {$skipped}");
        return self::SUCCESS;
    }

    protected function sendStep(DripStep $step, DripEnrollment $enrollment): void
    {
        $lead = $enrollment->lead;
        if (!$lead) return;

        $body    = $this->personalize($step->body ?? '', $lead);
        $subject = $this->personalize($step->subject ?? 'Following up', $lead);

        if ($step->channel === 'sms') {
            $phone = $lead->primary_phone ?? $lead->phone;
            if (!$phone) return;

            try {
                app(TwilioService::class)->sendSms($phone, $body, $lead->team_id);
            } catch (\Throwable $e) {
                Log::warning("Drip SMS failed for lead {$lead->id}: " . $e->getMessage());
            }

            SmsLog::create([
                'team_id'   => $lead->team_id,
                'lead_id'   => $lead->id,
                'sent_at'   => now(),
                'direction' => 'outbound',
                'message'   => $body,
                'notes'     => "Drip campaign #{$enrollment->campaign_id} step {$step->step_order}",
            ]);
        } else {
            $email = $lead->primary_email ?? $lead->email;
            if (!$email) return;

            Mail::raw($body, function ($msg) use ($subject, $email) {
                $msg->to($email)->subject($subject);
            });

            EmailLog::create([
                'team_id'      => $lead->team_id,
                'lead_id'      => $lead->id,
                'direction'    => 'outbound',
                'subject'      => $subject,
                'from_address' => config('mail.from.address'),
                'to_address'   => $email,
                'body'         => $body,
                'sent_at'      => now(),
            ]);
        }
    }

    protected function personalize(string $template, $lead): string
    {
        $first = $lead->first_name
            ?: (explode(' ', (string) ($lead->owner_name ?? ''))[0] ?? 'there');

        return str_replace(
            ['{{first_name}}', '{{owner_name}}', '{{phone}}', '{{email}}'],
            [$first, $lead->owner_name ?? $first, $lead->phone ?? '', $lead->email ?? ''],
            $template
        );
    }
}
