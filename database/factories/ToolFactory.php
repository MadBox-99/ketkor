<?php

namespace Database\Factories;

use App\Enums\ProductCategory;
use App\Models\Tool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tool>
 */
class ToolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'category' => $this->faker->randomElement(ProductCategory::class),
            'tag' => strtoupper($this->faker->bothify('??-####')),
            'factory_name' => $this->faker->company(),
        ];
    }
}
