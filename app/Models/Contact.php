<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Contact extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'team_id',
        'first_name',
        'last_name',
        'company',
        'email',
        'phone_primary',
        'phone_secondary',
        'status',
        'source',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim(collect([$this->first_name, $this->last_name])->filter()->implode(' ')) ?: (string) $this->company,
        );
    }

    protected function fullAddress(): Attribute
    {
        $parts = collect([
            $this->address_line1,
            $this->address_line2,
            trim("{$this->city} {$this->state} {$this->postal_code}"),
        ])->filter();

        return Attribute::make(
            get: fn () => $parts->implode(', '),
        );
    }

    public function activities(): HasMany
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject')->latest('created_at');
    }

    public function coldLeads(): HasMany
    {
        return $this->hasMany(ColdLead::class);
    }

    public function sellerLeads(): HasMany
    {
        return $this->hasMany(SellerLead::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function communicationLogs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'contact_property')
            ->withPivot(['sent_at', 'notes'])
            ->withTimestamps();
    }

    public function getFilamentGlobalSearchResultTitle(): string
    {
        $name = $this->full_name ?: 'Contact';
        $address = $this->full_address;
        $status = $this->status ?: 'Unknown';

        return trim("{$name} | {$address} | {$status}", " |");
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('contact')
            ->logOnly([
                'first_name',
                'last_name',
                'company',
                'email',
                'phone_primary',
                'phone_secondary',
                'status',
                'source',
                'address_line1',
                'address_line2',
                'city',
                'state',
                'postal_code',
            ])
            ->logOnlyDirty();
    }
}
