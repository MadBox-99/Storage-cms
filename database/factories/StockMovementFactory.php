<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MovementStatus;
use App\Enums\MovementType;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockMovement>
 */
final class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'movement_number' => 'MV-'.fake()->unique()->numerify('######'),
            'type' => fake()->randomElement(MovementType::cases()),
            'source_warehouse_id' => Warehouse::factory(),
            'target_warehouse_id' => Warehouse::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 100),
            'status' => MovementStatus::PLANNED,
            'reason' => fake()->sentence(),
        ];
    }

    public function planned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MovementStatus::PLANNED,
            'executed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MovementStatus::COMPLETED,
            'executed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MovementStatus::CANCELLED,
        ]);
    }

    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MovementType::TRANSFER,
            'source_warehouse_id' => Warehouse::factory(),
            'target_warehouse_id' => Warehouse::factory(),
        ]);
    }

    public function inbound(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MovementType::INBOUND,
            'source_warehouse_id' => null,
            'target_warehouse_id' => Warehouse::factory(),
        ]);
    }

    public function outbound(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MovementType::OUTBOUND,
            'source_warehouse_id' => Warehouse::factory(),
            'target_warehouse_id' => null,
        ]);
    }
}
