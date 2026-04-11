<?php

namespace App\Models;

use App\Models\Concerns\HasTeamScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory, HasTeamScope;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'related_type',
        'related_id',
        'assigned_to_id',
        'status',
        'priority',
        'team_id',
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function relatedLead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'related_id')->where('related_type', 'Lead');
    }

    public function relatedDeal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'related_id')->where('related_type', 'Deal');
    }

    public function relatedProperty(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'related_id')->where('related_type', 'Property');
    }
}
