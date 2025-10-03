<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CountryCode;
use App\Enums\SupplierRating;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
final class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $company = fake()->company();
        $countryCode = fake()->randomElement(CountryCode::class);

        return [
            'code' => 'SUP-'.fake()->unique()->numberBetween(1000, 9999),
            'company_name' => $company,
            'trade_name' => $company.' '.fake()->companySuffix(),
            'headquarters' => [
                'street' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'zip' => fake()->postcode(),
                'country' => $countryCode,
            ],
            'mailing_address' => [
                'street' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'zip' => fake()->postcode(),
                'country' => fake()->randomElement(CountryCode::class),
            ],
            'country_code' => $countryCode,
            'is_eu_member' => $countryCode->isEuMember(),
            'tax_number' => fake()->numerify('##########'),
            'eu_tax_number' => fake()->optional()->regexify('[A-Z]{2}[0-9]{10}'),
            'company_registration_number' => fake()->numerify('##-##-######'),
            'bank_account_number' => fake()->iban(),
            'contact_person' => fake()->name(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'website' => fake()->optional()->url(),
            'rating' => fake()->randomElement(SupplierRating::class),
            'is_active' => fake()->boolean(90), // 90% chance of being active
        ];
    }

    public function excellent(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => SupplierRating::EXCELLENT,
        ]);
    }

    public function blacklisted(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => SupplierRating::BLACKLISTED,
            'is_active' => false,
        ]);
    }
}
