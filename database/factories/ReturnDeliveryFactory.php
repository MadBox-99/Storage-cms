<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReturnReason;
use App\Enums\ReturnStatus;
use App\Enums\ReturnType;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReturnDelivery>
 */
final class ReturnDeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(ReturnType::cases());

        return [
            'return_number' => 'RET-'.fake()->unique()->numerify('######'),
            'type' => $type,
            'order_id' => $type === ReturnType::CUSTOMER_RETURN ? Order::factory() : null,
            'customer_id' => $type === ReturnType::CUSTOMER_RETURN ? Customer::factory() : null,
            'supplier_id' => $type === ReturnType::SUPPLIER_RETURN ? Supplier::factory() : null,
            'warehouse_id' => Warehouse::factory(),
            'processed_by' => Employee::factory(),
            'return_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'status' => fake()->randomElement(ReturnStatus::cases()),
            'reason' => fake()->randomElement(ReturnReason::cases()),
            'total_amount' => fake()->randomFloat(2, 1000, 50000),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function customerReturn(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ReturnType::CUSTOMER_RETURN,
            'order_id' => Order::factory(),
            'customer_id' => Customer::factory(),
            'supplier_id' => null,
        ]);
    }

    public function supplierReturn(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ReturnType::SUPPLIER_RETURN,
            'order_id' => null,
            'customer_id' => null,
            'supplier_id' => Supplier::factory(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReturnStatus::DRAFT,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReturnStatus::APPROVED,
        ]);
    }
}
