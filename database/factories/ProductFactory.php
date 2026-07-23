<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Tool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_name' => fake()->name(),
            'installer_name' => fake()->name(),
            'city' => fake()->city(),
            'street' => fake()->streetAddress(),
            'zip' => fake()->numberBetween(1000, 9999),
            'purchase_place' => fake()->company(),
            'serial_number' => strtoupper(fake()->bothify('??-####-????')),
            'purchase_date' => fake()->dateTimeBetween('-2 years', '-1 month'),
            'installation_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'warrantee_date' => fake()->dateTimeBetween('now', '+5 years'),
            'tool_id' => Tool::factory(),
            'comments' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * Indicate that the product has an expired warranty.
     */
    public function expiredWarranty(): static
    {
        return $this->state(fn (): array => [
            'warrantee_date' => fake()->dateTimeBetween('-2 years', '-1 day'),
        ]);
    }

    /**
     * Indicate that the product has a long warranty.
     */
    public function longWarranty(): static
    {
        return $this->state(fn (): array => [
            'warrantee_date' => fake()->dateTimeBetween('+3 years', '+10 years'),
        ]);
    }

    /**
     * Indicate that the product was recently purchased.
     */
    public function recentlyPurchased(): static
    {
        return $this->state(fn (): array => [
            'purchase_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'installation_date' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the product has detailed information.
     */
    public function withDetails(): static
    {
        return $this->state(fn (): array => [
            'owner_name' => fake()->name(),
            'installer_name' => fake()->name(),
            'comments' => fake()->paragraph(),
        ]);
    }
}
