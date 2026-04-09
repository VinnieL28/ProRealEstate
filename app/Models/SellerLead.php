<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SellerLead extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'contact_id',
        'property_address',
        'status',
        'campaign_name',
        'drip_status',
        'drip_step',
        'team_assigned',
        'lead_source',
        'tags',
        'notes',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(SellerLeadOffer::class);
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function getFilamentGlobalSearchResultTitle(): string
    {
        $seller = optional($this->contact)->full_name ?? 'Seller';
        $address = $this->property_address ?? optional($this->contact)->full_address ?? 'N/A';
        $status = $this->status ?? 'Unknown';

        return "{$seller} | {$address} | {$status}";
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('seller_lead')
            ->logOnly([
                'property_address',
                'status',
                'campaign_name',
                'drip_status',
                'drip_step',
                'team_assigned',
                'lead_source',
            ])
            ->logOnlyDirty();
    }
}
