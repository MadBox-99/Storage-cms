<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CnCode>
 */
final class CnCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->numerify('########'),
            'description' => fake()->sentence(4),
            'supplementary_unit' => fake()->randomElement(['liter', 'darab', 'kg', null]),
        ];
    }
}
