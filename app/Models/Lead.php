<?php

namespace App\Models;

use App\Events\LeadCreatedEvent;
use App\Events\LeadStageChangedEvent;
use App\Notifications\HotLeadFlagged;
use App\Notifications\NewLeadAssigned;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'owner_name',
        'phone',
        'email',
        'lead_source',
        'motivation_level',
        'asking_price',
        'max_offer',
        'last_offer',
        'stage',
        'assigned_to_id',
        'active_deal_id',
        'notes',
        'tags',
        'occupancy_status',
        'sell_timeline',
        'sell_reason',
        'listed_with_agent',
        'past_due_notice',
        'min_cash_offer',
        'open_to_owner_financing',
        'ownership_duration',
        'rental_unit_count',
        'annual_taxes',
        'annual_insurance',
        'utilities_metered',
        'utilities_payer',
        'unit_mix',
        'vacancies',
        'deferred_maintenance',
        'first_name',
        'last_name',
        'primary_phone',
        'primary_email',
        'major_market',
        'property_address',
        'owner_mailing_address',
        'mortgage_on_house',
        'tenant_lease_type',
        'property_manager_name',
        'score',
    ];

    protected $casts = [
        'tags'          => 'array',
        'asking_price'  => 'decimal:2',
        'max_offer'     => 'decimal:2',
        'last_offer'    => 'decimal:2',
    ];

    /**
     * Compute a 0–10 hot score based on motivation signals.
     *
     * Scoring:
     *   motivation_level (1–5) × 2            = 0–10 base
     *   sell_timeline = 'asap'                = +2
     *   past_due_notice = true                = +1.5
     *   deferred_maintenance = true           = +0.5
     *   listed_with_agent = true              = −2  (less off-market urgency)
     *   Clamped to 0–10
     */
    public function getHotScoreAttribute(): float
    {
        $score = ($this->motivation_level ?? 0) * 2;

        if ($this->sell_timeline === 'asap')  $score += 2;
        if ($this->past_due_notice)            $score += 1.5;
        if ($this->deferred_maintenance)       $score += 0.5;
        if ($this->listed_with_agent)          $score -= 2;

        return (float) max(0, min(10, $score));
    }

    /**
     * Calculate a 1–100 lead score for storage in the `score` column.
     *
     * Points breakdown (max = 100):
     *   Motivation (1-5) × 8             =  0–40
     *   Stage progress                    =  0–15
     *   Recency (days since last touch)   =  0–20
     *   Lead source quality               =  0–10
     *   sell_timeline = asap              = +5
     *   past_due_notice                   = +5
     *   deferred_maintenance              = +3
     *   listed_with_agent                 = -10 (less urgency)
     */
    public function calculateScore(): int
    {
        $pts = 0;

        // Motivation
        $pts += ($this->motivation_level ?? 0) * 8;  // 0–40

        // Stage
        $pts += match ($this->stage) {
            'new_lead'        => 3,
            'no_contact'      => 2,
            'contact_made'    => 5,
            'appointment_set' => 10,
            'due_diligence'   => 12,
            'offer_made'      => 15,
            'under_contract'  => 15,
            default           => 0,
        };

        // Recency
        $daysSince = (int) now()->diffInDays($this->updated_at ?? $this->created_at);
        $pts += match (true) {
            $daysSince <= 1  => 20,
            $daysSince <= 3  => 15,
            $daysSince <= 7  => 10,
            $daysSince <= 14 => 5,
            default          => 0,
        };

        // Source quality
        $pts += match ($this->lead_source ?? '') {
            'PPC', 'Referral' => 10,
            'Agent'           => 8,
            'Cold Call', 'SMS'=> 5,
            'D4D'             => 3,
            default           => 2,
        };

        // Urgency signals
        if ($this->sell_timeline === 'asap')   $pts += 5;
        if ($this->past_due_notice)            $pts += 5;
        if ($this->deferred_maintenance)       $pts += 3;
        if ($this->listed_with_agent)          $pts -= 10;

        return max(1, min(100, $pts));
    }

    /**
     * Whether this lead qualifies as "hot": score ≥ 7 and not closed.
     */
    public function getIsHotAttribute(): bool
    {
        return $this->hot_score >= 7
            && !in_array($this->stage, ['closed_won', 'closed_lost'], true);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function activeDeal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'active_deal_id');
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class);
    }

    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'related_id')->where('related_type', 'Lead');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'related_id')->where('related_type', 'Lead');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'related_id')->where('related_type', 'Lead');
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    protected static function booted(): void
    {
        static::created(function (Lead $lead) {
            LeadCreatedEvent::dispatch($lead);

            // Notify the assigned agent
            if ($lead->assigned_to_id) {
                $assignee = \App\Models\User::find($lead->assigned_to_id);
                $assignee?->notify(new NewLeadAssigned($lead));
            }
        });

        static::updated(function (Lead $lead) {
            $originalStage = $lead->getOriginal('stage');
            $newStage = $lead->stage;

            // Notify new assignee when reassigned
            if ($lead->wasChanged('assigned_to_id') && $lead->assigned_to_id) {
                $assignee = \App\Models\User::find($lead->assigned_to_id);
                $assignee?->notify(new NewLeadAssigned($lead));
            }

            // Notify when lead becomes hot
            if ($lead->wasChanged('motivation_level') && $lead->is_hot && $lead->assigned_to_id) {
                $assignee = \App\Models\User::find($lead->assigned_to_id);
                $assignee?->notify(new HotLeadFlagged($lead));
            }

            if ($originalStage !== $newStage) {
                LeadStageChangedEvent::dispatch($lead, (string) $originalStage, (string) $newStage);

                Activity::create([
                    'team_id' => $lead->team_id,
                    'user_id' => auth()->id(),
                    'related_type' => 'Lead',
                    'related_id' => $lead->id,
                    'type' => 'status_change',
                    'description' => "Stage changed from {$originalStage} to {$newStage}",
                ]);

                if ($newStage === 'under_contract') {
                    Task::create([
                        'team_id' => $lead->team_id,
                        'title' => 'Prepare contract package',
                        'description' => 'Auto-created on stage change.',
                        'due_date' => now()->addDays(2),
                        'related_type' => 'Lead',
                        'related_id' => $lead->id,
                        'status' => 'open',
                        'priority' => 'high',
                        'assigned_to_id' => $lead->assigned_to_id,
                    ]);
                }

                if ($newStage === 'closed_won') {
                    foreach ($lead->properties as $property) {
                        $property->update(['status' => 'sold']);
                    }
                }
            }
        });
    }
}
