<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\SellerLead;
use Illuminate\Database\Eloquent\Factories\Factory;

class SellerLeadFactory extends Factory
{
    protected $model = SellerLead::class;

    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'property_address' => $this->faker->streetAddress(),
            'status' => $this->faker->randomElement(['warm', 'appointment_set', 'under_contract']),
            'campaign_name' => $this->faker->randomElement(['FSBO Campaign', 'Follow Up', 'Direct Mail']),
            'drip_status' => $this->faker->randomElement(['new', 'active', 'paused']),
            'drip_step' => $this->faker->numberBetween(0, 6),
            'team_assigned' => $this->faker->randomElement(['Leo', 'Amber', 'Ella']),
            'lead_source' => $this->faker->randomElement(['FSBO', 'FRBO', 'Referral']),
            'tags' => [$this->faker->randomElement(['hot', 'negotiating', 'watch'])],
            'notes' => $this->faker->sentence(10),
        ];
    }
}
