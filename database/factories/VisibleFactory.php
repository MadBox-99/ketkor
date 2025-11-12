<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use App\Models\Visible;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visible>
 */
class VisibleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'isVisible' => fake()->boolean(80),
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
        ];
    }
}
