<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Transaction extends Model
{
    use HasFactory;
    use LogsActivity;

    public const TYPE_INVESTMENT = 'investment';
    public const TYPE_LISTING = 'listing';

    protected $fillable = [
        'contact_id',
        'type',
        'seller_name',
        'property_address',
        'status',
        'deal_stage',
        'list_cost',
        'skiptrace_cost',
        'gross_revenue',
        'net_revenue',
        'social_media_posted_at',
        'closed_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'list_cost' => 'decimal:2',
        'skiptrace_cost' => 'decimal:2',
        'gross_revenue' => 'decimal:2',
        'net_revenue' => 'decimal:2',
        'social_media_posted_at' => 'datetime',
        'closed_at' => 'datetime',
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

    public function getFilamentGlobalSearchResultTitle(): string
    {
        $seller = $this->seller_name ?: optional($this->contact)->full_name ?: 'Seller';
        $address = $this->property_address ?: optional($this->contact)->full_address ?: 'N/A';
        $status = $this->deal_stage ?: $this->status ?: 'Unknown';

        return "{$seller} || {$address} || {$status}";
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('transaction')
            ->logOnly([
                'type',
                'seller_name',
                'property_address',
                'status',
                'deal_stage',
                'list_cost',
                'skiptrace_cost',
                'gross_revenue',
                'net_revenue',
                'social_media_posted_at',
            ])
            ->logOnlyDirty();
    }
}
