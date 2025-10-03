<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Enums\UnitType;
use App\Models\Category;
use App\Models\Supplier;
use Bezhanov\Faker\Provider\Commerce;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
final class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        fake()->addProvider(new Commerce(fake()));

        return [
            'sku' => 'SKU-'.fake()->unique()->numerify('#####'),
            'name' => fake()->productName,
            'description' => fake()->sentence(),
            'barcode' => fake()->unique()->ean13(),
            'unit_of_measure' => fake()->randomElement(UnitType::class),
            'weight' => fake()->randomFloat(2, 0.1, 100),
            'dimensions' => [
                'length' => fake()->randomFloat(2, 1, 100),
                'width' => fake()->randomFloat(2, 1, 100),
                'height' => fake()->randomFloat(2, 1, 100),
                'unit' => 'cm',
            ],
            'category_id' => Category::factory(),
            'supplier_id' => Supplier::factory(),
            'min_stock' => fake()->numberBetween(5, 20),
            'max_stock' => fake()->numberBetween(100, 500),
            'reorder_point' => fake()->numberBetween(10, 50),
            'price' => fake()->randomFloat(2, 1, 1000),
            'status' => fake()->randomElement(ProductStatus::class),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductStatus::ACTIVE,
        ]);
    }

    public function discontinued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductStatus::DISCONTINUED,
            'max_stock' => 0,
            'reorder_point' => 0,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductStatus::OUT_OF_STOCK,
        ]);
    }
}
