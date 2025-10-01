<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StockTransactionType;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockTransaction>
 */
final class StockTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitCost = fake()->randomFloat(4, 1, 1000);
        $quantity = fake()->numberBetween(1, 100);

        return [
            'stock_id' => Stock::factory(),
            'product_id' => Product::factory(),
            'warehouse_id' => Warehouse::factory(),
            'type' => fake()->randomElement(StockTransactionType::class),
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $unitCost * $quantity,
            'remaining_quantity' => $quantity,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
