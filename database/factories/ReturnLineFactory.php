<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProductCondition;
use App\Enums\ReturnReason;
use App\Models\Product;
use App\Models\ReturnDelivery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReturnLine>
 */
final class ReturnLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'return_delivery_id' => ReturnDelivery::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 20),
            'unit_price' => fake()->randomFloat(2, 100, 5000),
            'condition' => fake()->randomElement(ProductCondition::cases()),
            'return_reason' => fake()->randomElement(ReturnReason::cases()),
            'batch_number' => fake()->optional()->bothify('BATCH-####'),
            'note' => fake()->optional()->sentence(),
        ];
    }

    public function goodCondition(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => ProductCondition::GOOD,
        ]);
    }

    public function damaged(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => ProductCondition::DAMAGED,
            'return_reason' => ReturnReason::DAMAGED,
        ]);
    }
}
