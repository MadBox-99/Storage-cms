<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StockStatus;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock>
 */
final class StockFactory extends Factory
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
            'warehouse_id' => Warehouse::factory(),
            'quantity' => fake()->numberBetween(0, 500),
            'reserved_quantity' => 0,
            'minimum_stock' => fake()->numberBetween(5, 20),
            'maximum_stock' => fake()->numberBetween(100, 1000),
            'status' => fake()->randomElement(StockStatus::class),
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => fake()->numberBetween(0, 5),
            'minimum_stock' => 10,
            'status' => StockStatus::LOW_STOCK,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
            'status' => StockStatus::OUT_OF_STOCK,
        ]);
    }
}
