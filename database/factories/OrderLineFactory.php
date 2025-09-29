<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderLine>
 */
final class OrderLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 1, 500);
        $quantity = fake()->numberBetween(1, 20);
        $discountPercent = fake()->randomElement([0, 0, 0, 5, 10, 15, 20]); // Most have no discount

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_percent' => $discountPercent,
            'note' => fake()->optional()->sentence(),
        ];
    }

    public function withoutDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_percent' => 0,
        ]);
    }

    public function withDiscount(float $percent): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_percent' => $percent,
        ]);
    }
}