<?php

namespace App\Services\Kpi;

use App\Models\KpiMetric;
use Carbon\CarbonImmutable;

class KpiMetricRecorder
{
    public function record(
        string $metricKey,
        float $value = 1,
        ?CarbonImmutable $occurredOn = null,
        ?string $source = 'app',
        array $context = []
    ): KpiMetric {
        $occurredOn ??= CarbonImmutable::today();

        $metric = KpiMetric::firstOrNew([
            'metric_key' => $metricKey,
            'occurred_on' => $occurredOn,
            'source' => $source,
        ]);

        $metric->metric_value = (float) $metric->metric_value + $value;
        $metric->context = array_merge($metric->context ?? [], $context);
        $metric->save();

        return $metric;
    }
}
