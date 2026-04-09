<?php

namespace Database\Factories;

use App\Models\ColdLead;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class ColdLeadFactory extends Factory
{
    protected $model = ColdLead::class;

    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'status' => $this->faker->randomElement(['new', 'contact_attempted', 'paused']),
            'campaign_name' => $this->faker->randomElement(['SMS Drip', 'RVM', 'Cold Call']),
            'drip_status' => $this->faker->randomElement(['new', 'contact_attempted', 'active', 'paused']),
            'drip_step' => $this->faker->numberBetween(0, 5),
            'team_assigned' => $this->faker->randomElement(['Marketing', 'Client Care', 'Acquisition']),
            'notes' => $this->faker->sentence(8),
            'tags' => [$this->faker->randomElement(['priority', 'follow-up', 'nurture'])],
            'metadata' => [],
        ];
    }
}
