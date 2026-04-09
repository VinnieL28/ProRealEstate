<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_id',
        'default_currency',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps()->withPivot('role_override');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class);
    }

    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(Setting::class);
    }
}
