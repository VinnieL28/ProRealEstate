<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CommunicationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'loggable_id',
        'loggable_type',
        'occurred_at',
        'medium',
        'number',
        'number_name',
        'direction',
        'recording_url',
        'duration_secs',
        'payload',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function formattedForDisplay(): string
    {
        $timestamp = optional($this->occurred_at)->format('m/d/Y H:i') ?? 'N/A';
        $recording = $this->recording_url ?: 'Pending';
        $duration = $this->duration_secs ? "{$this->duration_secs}" : '0';

        return sprintf(
            '[%s] || [%s] || [%s] || [%s] || [%s] || [%s]',
            $timestamp,
            ucfirst($this->medium),
            $this->number ?? '-',
            $this->number_name ?? '-',
            $recording,
            $duration
        );
    }
}
