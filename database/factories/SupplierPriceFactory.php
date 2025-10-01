<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupplierPrice>
 */
final class SupplierPriceFactory extends Factory
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
            'supplier_id' => Supplier::factory(),
            'price' => fake()->randomFloat(4, 1, 10000),
            'currency' => 'HUF',
            'minimum_order_quantity' => fake()->numberBetween(1, 100),
            'lead_time_days' => fake()->numberBetween(1, 30),
            'valid_from' => now()->subDays(30),
            'valid_until' => now()->addDays(90),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'valid_from' => now()->subDays(30),
            'valid_until' => now()->addDays(90),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => now()->subDays(90),
            'valid_until' => now()->subDays(30),
        ]);
    }
}
