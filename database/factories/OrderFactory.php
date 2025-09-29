<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
final class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => 'ORD-'.fake()->unique()->numberBetween(10000, 99999),
            'type' => OrderType::SALES, // Default to sales
            'customer_id' => null,
            'supplier_id' => null,
            'status' => OrderStatus::DRAFT,
            'order_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'delivery_date' => fake()->dateTimeBetween('now', '+30 days'),
            'total_amount' => fake()->randomFloat(2, 10, 5000),
            'shipping_address' => [
                'street' => fake()->streetAddress(),
                'city' => fake()->city(),
                'zip' => fake()->postcode(),
                'country' => fake()->country(),
            ],
        ];
    }

    public function withCustomer(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => Customer::factory(),
            'type' => OrderType::SALES,
        ]);
    }

    public function withSupplier(): static
    {
        return $this->state(fn (array $attributes) => [
            'supplier_id' => Supplier::factory(),
            'type' => OrderType::PURCHASE,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::DRAFT,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::CONFIRMED,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::PROCESSING,
        ]);
    }

    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::SHIPPED,
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::DELIVERED,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::CANCELLED,
        ]);
    }

    public function purchaseOrder(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => OrderType::PURCHASE,
            'supplier_id' => Supplier::factory(),
            'customer_id' => null,
        ]);
    }

    public function salesOrder(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => OrderType::SALES,
            'customer_id' => Customer::factory(),
            'supplier_id' => null,
        ]);
    }

    public function transferOrder(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => OrderType::TRANSFER,
            'customer_id' => null,
            'supplier_id' => null,
        ]);
    }
}
