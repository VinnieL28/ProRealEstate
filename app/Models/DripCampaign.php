<?php

namespace App\Models;

use App\Models\Concerns\HasTeamScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DripCampaign extends Model
{
    use HasFactory, HasTeamScope;

    protected $fillable = [
        'team_id', 'name', 'description', 'trigger_type', 'trigger_stage', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(DripStep::class, 'campaign_id')->orderBy('step_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(DripEnrollment::class, 'campaign_id');
    }
}
