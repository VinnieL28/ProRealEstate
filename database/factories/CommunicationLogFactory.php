<?php

namespace Database\Factories;

use App\Models\CommunicationLog;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommunicationLogFactory extends Factory
{
    protected $model = CommunicationLog::class;

    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'loggable_id' => null,
            'loggable_type' => null,
            'occurred_at' => $this->faker->dateTimeBetween('-10 days', 'now'),
            'medium' => $this->faker->randomElement(['call', 'text']),
            'number' => $this->faker->phoneNumber(),
            'number_name' => $this->faker->word(),
            'direction' => $this->faker->randomElement(['inbound', 'outbound']),
            'recording_url' => $this->faker->url(),
            'duration_secs' => $this->faker->numberBetween(30, 600),
            'payload' => $this->faker->sentence(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (CommunicationLog $log): void {
            if (!$log->loggable_id) {
                $log->loggable_id = $log->contact_id;
                $log->loggable_type = Contact::class;
                $log->save();
            }
        });
    }
}
