<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProductLogType;
use App\Models\Product;
use App\Models\ProductLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductLog>
 */
class ProductLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'what' => ProductLogType::Maintenance,
            'comment' => fake()->optional(0.3)->sentence(),
            'when' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * A munkalap beüzemelést rögzít, nem karbantartást.
     */
    public function installation(): static
    {
        return $this->state(fn (): array => [
            'what' => ProductLogType::Installation,
        ]);
    }

    /**
     * A munkalap egy adott napon készült.
     */
    public function on(string $date): static
    {
        return $this->state(fn (): array => [
            'when' => $date,
        ]);
    }
}
