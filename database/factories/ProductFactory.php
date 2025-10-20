<?php

namespace Database\Factories;

use App\Models\Product;
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
            'owner_name' => $this->faker->name(),
            'installer_name' => $this->faker->name(),
            'city' => $this->faker->city(),
            'street' => $this->faker->streetAddress(),
            'zip' => $this->faker->numberBetween(1000, 9999),
            'purchase_place' => $this->faker->company(),
            'serial_number' => strtoupper($this->faker->bothify('??-####-????')),
            'purchase_date' => $this->faker->dateTimeBetween('-2 years', '-1 month'),
            'installation_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'warrantee_date' => $this->faker->dateTimeBetween('now', '+5 years'),
            'tool_id' => \App\Models\Tool::factory(),
            'comments' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    /**
     * Indicate that the product has an expired warranty.
     */
    public function expiredWarranty(): static
    {
        return $this->state(fn () => [
            'warrantee_date' => $this->faker->dateTimeBetween('-2 years', '-1 day'),
        ]);
    }

    /**
     * Indicate that the product has a long warranty.
     */
    public function longWarranty(): static
    {
        return $this->state(fn () => [
            'warrantee_date' => $this->faker->dateTimeBetween('+3 years', '+10 years'),
        ]);
    }

    /**
     * Indicate that the product was recently purchased.
     */
    public function recentlyPurchased(): static
    {
        return $this->state(fn () => [
            'purchase_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'installation_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the product has detailed information.
     */
    public function withDetails(): static
    {
        return $this->state(fn () => [
            'owner_name' => $this->faker->name(),
            'installer_name' => $this->faker->name(),
            'comments' => $this->faker->paragraph(),
        ]);
    }
}
