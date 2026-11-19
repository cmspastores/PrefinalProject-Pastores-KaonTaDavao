<?php

namespace Database\Factories;

use App\Models\Dish;
use App\Models\Eatery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dish>
 */
class DishFactory extends Factory
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
            'name' => fake()->words(2, true),
            'price' => fake()->randomFloat(2, 40, 500),
            'is_bestseller' => fake()->boolean(20),
        ];
    }
}
