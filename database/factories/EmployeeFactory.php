<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
final class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'warehouse_id' => Warehouse::factory(),
            'employee_code' => fake()->unique()->bothify('EMP-####'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'position' => fake()->randomElement([
                'Warehouse Manager',
                'Warehouse Supervisor',
                'Stock Controller',
                'Receiving Clerk',
                'Shipping Clerk',
                'Forklift Operator',
                'Inventory Analyst',
                'Order Picker',
            ]),
            'department' => fake()->randomElement([
                'Warehouse Operations',
                'Inventory Management',
                'Receiving',
                'Shipping',
                'Logistics',
            ]),
            'phone' => fake()->phoneNumber(),
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
