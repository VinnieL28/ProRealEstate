<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'type' => $this->faker->randomElement([Transaction::TYPE_INVESTMENT, Transaction::TYPE_LISTING]),
            'seller_name' => $this->faker->name(),
            'property_address' => $this->faker->address(),
            'status' => $this->faker->randomElement(['new', 'processing', 'closed']),
            'deal_stage' => $this->faker->randomElement(['Offers Made', 'Under Contract', 'Closed']),
            'list_cost' => $this->faker->randomFloat(2, 1000, 5000),
            'skiptrace_cost' => $this->faker->randomFloat(2, 200, 1200),
            'gross_revenue' => $this->faker->randomFloat(2, 15000, 75000),
            'net_revenue' => $this->faker->randomFloat(2, 5000, 45000),
            'social_media_posted_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'closed_at' => $this->faker->optional()->dateTimeBetween('-2 months', 'now'),
            'notes' => $this->faker->sentence(12),
            'metadata' => [],
        ];
    }
}
