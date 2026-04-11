<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'team_id',
        'company_name',
        'company_logo',
        'timezone',
        'default_currency',
        'pipeline_stages',
        'lead_sources',
        'team_phone_numbers',
        'twilio_sid',
        'twilio_token',
        'twilio_phone_number',
        'gmail_client_id',
        'gmail_client_secret',
        'gmail_refresh_token',
        'docusign_client_id',
        'docusign_secret',
        'docusign_account_id',
        'file_storage_driver',
        'inbox_provider',
        'email_provider',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'smtp_from_address',
        'smtp_from_name',
    ];

    protected $casts = [
        'pipeline_stages' => 'array',
        'lead_sources' => 'array',
        'team_phone_numbers' => 'array',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get (and cache) the settings for a given team. Cache is invalidated on save.
     */
    public static function forTeam(int $teamId): ?self
    {
        return Cache::rememberForever("team_settings_{$teamId}", fn () => self::where('team_id', $teamId)->first());
    }

    protected static function booted(): void
    {
        static::saved(function (self $setting) {
            Cache::forget("team_settings_{$setting->team_id}");
        });

        static::deleted(function (self $setting) {
            Cache::forget("team_settings_{$setting->team_id}");
        });
    }
}
