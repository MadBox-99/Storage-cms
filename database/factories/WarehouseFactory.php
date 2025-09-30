<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WarehouseType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Warehouse>
 */
final class WarehouseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify('WH-???###'),
            'name' => fake()->company().' Warehouse',
            'address' => fake()->address(),
            'type' => fake()->randomElement(WarehouseType::class),
            'capacity' => fake()->numberBetween(1000, 10000),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
