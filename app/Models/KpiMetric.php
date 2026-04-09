<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'metric_key',
        'occurred_on',
        'metric_value',
        'source',
        'context',
    ];

    protected $casts = [
        'occurred_on' => 'date',
        'metric_value' => 'decimal:2',
        'context' => 'array',
    ];

    public static function forToday(string $metricKey, ?string $source = null): self
    {
        return static::firstOrCreate([
            'metric_key' => $metricKey,
            'occurred_on' => CarbonImmutable::today(),
            'source' => $source,
        ], [
            'metric_value' => 0,
        ]);
    }
}
