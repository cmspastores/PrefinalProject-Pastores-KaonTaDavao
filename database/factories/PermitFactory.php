<?php

namespace Database\Factories;

use App\Models\Eatery;
use App\Models\Permit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permit>
 */
class PermitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'eatery_id' => Eatery::factory(),
            'permit_no' => sprintf('DVO-%04d-%04d', fake()->numberBetween(2024, 2028), fake()->unique()->numberBetween(0, 9999)),
            'expires_on' => fake()->dateTimeBetween('-1 year', '+2 years')->format('Y-m-d'),
        ];
    }
}
