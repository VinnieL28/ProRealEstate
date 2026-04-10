<?php

namespace App\Models;

use App\Events\DealClosedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'property_id',
        'lead_id',
        'name',
        'contract_type',
        'contract_date',
        'closing_date',
        'purchase_price',
        'assignment_fee',
        'sale_price',
        'closing_costs',
        'marketing_costs',
        'profit',
        'roi',
        'stage',
    ];

    protected $casts = [
        'contract_date' => 'date',
        'closing_date' => 'date',
        'purchase_price' => 'decimal:2',
        'assignment_fee' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'closing_costs' => 'decimal:2',
        'marketing_costs' => 'decimal:2',
        'profit' => 'decimal:2',
        'roi' => 'decimal:2',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'related_id')->where('related_type', 'Deal');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'related_id')->where('related_type', 'Deal');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'related_id')->where('related_type', 'Deal');
    }

    protected static function booted(): void
    {
        static::updated(function (Deal $deal) {
            $oldStage = $deal->getOriginal('stage');
            $newStage = $deal->stage;
            if ($oldStage !== $newStage && $newStage === 'closed_won') {
                DealClosedEvent::dispatch($deal);
            }
        });
    }

    public function getProfitAttribute(): ?float
    {
        if (!is_null($this->attributes['profit'] ?? null)) {
            return (float) $this->attributes['profit'];
        }

        $sale = $this->sale_price ?? $this->assignment_fee ?? 0;
        $costs = ($this->purchase_price ?? 0) + ($this->closing_costs ?? 0) + ($this->marketing_costs ?? 0);

        return $sale - $costs;
    }

    public function getRoiAttribute(): ?float
    {
        if (!is_null($this->attributes['roi'] ?? null)) {
            return (float) $this->attributes['roi'];
        }

        $costs = ($this->purchase_price ?? 0) + ($this->closing_costs ?? 0) + ($this->marketing_costs ?? 0);
        return $costs > 0 ? round(($this->profit / $costs) * 100, 2) : null;
    }
}
