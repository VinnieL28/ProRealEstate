<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DripStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id', 'step_order', 'channel', 'delay_days', 'subject', 'body',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(DripCampaign::class, 'campaign_id');
    }
}
