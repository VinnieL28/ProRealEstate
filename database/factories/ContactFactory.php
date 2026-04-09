<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        $first = $this->faker->firstName;
        $last = $this->faker->lastName;

        return [
            'first_name' => $first,
            'last_name' => $last,
            'company' => $this->faker->company,
            'email' => $this->faker->unique()->safeEmail(),
            'phone_primary' => $this->faker->e164PhoneNumber(),
            'phone_secondary' => $this->faker->optional()->e164PhoneNumber(),
            'status' => $this->faker->randomElement(['new', 'contacted', 'warm', 'client']),
            'source' => $this->faker->randomElement(['CallRail', 'BatchDialer', 'Website', 'Referral']),
            'address_line1' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'postal_code' => $this->faker->postcode(),
            'notes' => $this->faker->sentence(),
            'metadata' => [],
        ];
    }
}
