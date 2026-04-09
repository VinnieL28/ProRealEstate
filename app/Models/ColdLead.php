<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ColdLead extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'contact_id',
        'status',
        'campaign_name',
        'drip_status',
        'drip_step',
        'team_assigned',
        'notes',
        'tags',
        'metadata',
    ];

    protected $casts = [
        'tags' => 'array',
        'metadata' => 'array',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function scopeProcessable($query)
    {
        return $query->whereIn('drip_status', ['new', 'contact_attempted']);
    }

    public function getFilamentGlobalSearchResultTitle(): string
    {
        $seller = optional($this->contact)->full_name ?? 'Seller';
        $address = optional($this->contact)->full_address ?? 'N/A';
        $status = $this->status ?? 'Unknown';

        return "{$seller} | {$address} | {$status}";
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('cold_lead')
            ->logOnly([
                'status',
                'campaign_name',
                'drip_status',
                'drip_step',
                'team_assigned',
            ])
            ->logOnlyDirty();
    }
}
