<?php

namespace App\Models;

use App\Models\Concerns\HasTeamScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasTeamScope;
    protected $fillable = [
        'team_id',
        'lead_id',
        'deal_id',
        'property_id',
        'generated_by',
        'template_type',
        'title',
        'path',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function team(): BelongsTo    { return $this->belongsTo(Team::class); }
    public function lead(): BelongsTo   { return $this->belongsTo(Lead::class); }
    public function deal(): BelongsTo   { return $this->belongsTo(Deal::class); }
    public function property(): BelongsTo { return $this->belongsTo(Property::class); }
    public function generatedBy(): BelongsTo { return $this->belongsTo(User::class, 'generated_by'); }
}
