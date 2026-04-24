<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DripEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id', 'team_id', 'lead_id', 'current_step', 'status', 'enrolled_at', 'next_send_at',
    ];

    protected $casts = [
        'enrolled_at'  => 'datetime',
        'next_send_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(DripCampaign::class, 'campaign_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
