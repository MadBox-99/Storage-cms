<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CustomerType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
final class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_code' => 'CUST-'.fake()->unique()->numberBetween(10000, 99999),
            'name' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'billing_address' => [
                'street' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'postal_code' => fake()->postcode(),
                'country' => fake()->country(),
            ],
            'shipping_address' => [
                'street' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'postal_code' => fake()->postcode(),
                'country' => fake()->country(),
            ],
            'credit_limit' => fake()->randomFloat(2, 1000, 50000),
            'balance' => fake()->randomFloat(2, 0, 10000),
            'type' => fake()->randomElement(CustomerType::class),
        ];
    }
}
