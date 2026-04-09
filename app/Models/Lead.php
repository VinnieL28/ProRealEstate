<?php

namespace App\Models;

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
    ];

    protected $casts = [
        'tags' => 'array',
        'asking_price' => 'decimal:2',
        'max_offer' => 'decimal:2',
        'last_offer' => 'decimal:2',
    ];

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
        static::updated(function (Lead $lead) {
            $originalStage = $lead->getOriginal('stage');
            $newStage = $lead->stage;

            if ($originalStage !== $newStage) {
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
