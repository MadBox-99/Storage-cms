<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\IntrastatDeliveryTerms;
use App\Enums\IntrastatTransactionType;
use App\Enums\IntrastatTransportMode;
use App\Models\IntrastatDeclaration;
use App\Models\Order;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IntrastatLine>
 */
final class IntrastatLineFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 100);
        $unitPrice = fake()->randomFloat(2, 1000, 100000);
        $netMassPerUnit = fake()->randomFloat(3, 0.1, 50);

        return [
            'intrastat_declaration_id' => IntrastatDeclaration::factory(),
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'supplier_id' => Supplier::factory(),
            'cn_code' => fake()->numerify('########'), // 8 digit CN code
            'quantity' => $quantity,
            'net_mass' => $quantity * $netMassPerUnit,
            'supplementary_unit' => null,
            'supplementary_quantity' => null,
            'invoice_value' => $quantity * $unitPrice,
            'statistical_value' => $quantity * $unitPrice,
            'country_of_origin' => fake()->randomElement(['DE', 'AT', 'FR', 'IT', 'NL', 'PL', 'CZ', 'SK']),
            'country_of_consignment' => null,
            'country_of_destination' => null,
            'transaction_type' => fake()->randomElement(IntrastatTransactionType::cases()),
            'transport_mode' => fake()->randomElement(IntrastatTransportMode::cases()),
            'delivery_terms' => fake()->randomElement(IntrastatDeliveryTerms::cases()),
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function forArrival(): static
    {
        return $this->state(fn (array $attributes) => [
            'country_of_consignment' => fake()->randomElement(['DE', 'AT', 'FR', 'IT', 'NL']),
            'country_of_destination' => 'HU',
        ]);
    }

    public function forDispatch(): static
    {
        return $this->state(fn (array $attributes) => [
            'country_of_consignment' => 'HU',
            'country_of_destination' => fake()->randomElement(['DE', 'AT', 'FR', 'IT', 'NL']),
            'country_of_origin' => 'HU',
        ]);
    }

    public function withSupplementaryUnit(): static
    {
        return $this->state(function (array $attributes): array {
            $quantity = $attributes['quantity'] ?? fake()->randomFloat(2, 1, 100);

            return [
                'supplementary_unit' => fake()->randomElement(['p/st', 'kg', 'l', 'm']),
                'supplementary_quantity' => $quantity,
            ];
        });
    }
}
