<?php

namespace Database\Factories;

use App\Models\KpiMetric;
use Illuminate\Database\Eloquent\Factories\Factory;

class KpiMetricFactory extends Factory
{
    protected $model = KpiMetric::class;

    public function definition(): array
    {
        return [
            'metric_key' => $this->faker->randomElement([
                'leads.created',
                'leads.cold.drip.paused',
                'transactions.closed',
                'contacts.updated',
            ]),
            'occurred_on' => $this->faker->dateTimeBetween('-14 days', 'now'),
            'metric_value' => $this->faker->numberBetween(1, 20),
            'source' => 'app',
            'context' => [],
        ];
    }
}
