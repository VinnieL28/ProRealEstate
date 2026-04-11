<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;

class Team extends Model
{
    use HasFactory, Billable;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'owner_id',
        'default_currency',
        'subscription_plan',
        'trial_ends_at',
        'is_active',
        'onboarding_steps',
        'stripe_id',
        'pm_type',
        'pm_last_four',
    ];

    protected $casts = [
        'trial_ends_at'    => 'datetime',
        'is_active'        => 'boolean',
        'onboarding_steps' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Team $team) {
            if (empty($team->slug)) {
                $team->slug = Str::slug($team->name) . '-' . Str::random(4);
            }
            if (is_null($team->trial_ends_at)) {
                $team->trial_ends_at = now()->addDays(14);
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps()->withPivot('role_override');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class);
    }

    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(Setting::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    // ── Subscription helpers ────────────────────────────────────────────────

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function hasActiveAccess(): bool
    {
        return $this->is_active && (
            $this->subscribed('default') ||
            $this->isOnTrial()
        );
    }

    public function onboardingPercent(): int
    {
        $steps = $this->onboarding_steps ?? [];
        $total = 5; // steps 1-5 (step 6 is "done")
        $done  = count(array_filter($steps));
        return (int) min(100, round(($done / $total) * 100));
    }

    public function markOnboardingStep(int $step): void
    {
        $steps = $this->onboarding_steps ?? [];
        $steps[$step] = true;
        $this->update(['onboarding_steps' => $steps]);
    }
}
