<?php

namespace Database\Factories;

use App\Models\SellerLead;
use App\Models\SellerLeadOffer;
use Illuminate\Database\Eloquent\Factories\Factory;

class SellerLeadOfferFactory extends Factory
{
    protected $model = SellerLeadOffer::class;

    public function definition(): array
    {
        return [
            'seller_lead_id' => SellerLead::factory(),
            'buyer_name' => $this->faker->name(),
            'amount' => $this->faker->numberBetween(150000, 450000),
            'status' => $this->faker->randomElement(['pending', 'accepted', 'rejected']),
            'notes' => $this->faker->sentence(),
        ];
    }
}
