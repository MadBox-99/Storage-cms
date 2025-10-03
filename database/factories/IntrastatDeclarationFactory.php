<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\IntrastatDirection;
use App\Enums\IntrastatStatus;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IntrastatDeclaration>
 */
final class IntrastatDeclarationFactory extends Factory
{
    public function definition(): array
    {
        $referenceDate = DateTimeImmutable::createFromMutable(fake()->dateTimeBetween('-6 months', 'now'));
        $direction = fake()->randomElement(IntrastatDirection::cases());

        return [
            'declaration_number' => $this->generateDeclarationNumber($referenceDate, $direction),
            'direction' => $direction,
            'reference_year' => (int) $referenceDate->format('Y'),
            'reference_month' => (int) $referenceDate->format('m'),
            'declaration_date' => fake()->dateTimeBetween($referenceDate->format('Y-m-d'), 'now'),
            'submitted_at' => null,
            'submitted_by' => null,
            'total_invoice_value' => 0,
            'total_statistical_value' => 0,
            'total_net_mass' => 0,
            'notes' => fake()->optional()->sentence(),
            'status' => IntrastatStatus::DRAFT,
        ];
    }

    public function arrival(): static
    {
        return $this->state(function (array $attributes): array {
            $referenceDate = DateTimeImmutable::createFromFormat('Y-m', $attributes['reference_year'].'-'.$attributes['reference_month']);

            return [
                'direction' => IntrastatDirection::ARRIVAL,
                'declaration_number' => $this->generateDeclarationNumber($referenceDate, IntrastatDirection::ARRIVAL),
            ];
        });
    }

    public function dispatch(): static
    {
        return $this->state(function (array $attributes): array {
            $referenceDate = DateTimeImmutable::createFromFormat('Y-m', $attributes['reference_year'].'-'.$attributes['reference_month']);

            return [
                'direction' => IntrastatDirection::DISPATCH,
                'declaration_number' => $this->generateDeclarationNumber($referenceDate, IntrastatDirection::DISPATCH),
            ];
        });
    }

    public function ready(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IntrastatStatus::READY,
        ]);
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IntrastatStatus::SUBMITTED,
            'submitted_at' => fake()->dateTimeBetween($attributes['declaration_date'], 'now'),
            'submitted_by' => fake()->name(),
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IntrastatStatus::ACCEPTED,
            'submitted_at' => fake()->dateTimeBetween($attributes['declaration_date'], '-1 week'),
            'submitted_by' => fake()->name(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IntrastatStatus::REJECTED,
            'submitted_at' => fake()->dateTimeBetween($attributes['declaration_date'], '-1 week'),
            'submitted_by' => fake()->name(),
            'notes' => 'Elutasítva: '.fake()->sentence(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IntrastatStatus::CANCELLED,
            'notes' => 'Érvénytelenítve: '.fake()->sentence(),
        ]);
    }

    public function withTotals(?float $invoiceValue = null, ?float $statisticalValue = null, ?float $netMass = null): static
    {
        return $this->state(fn (array $attributes) => [
            'total_invoice_value' => $invoiceValue ?? fake()->randomFloat(2, 10000, 1000000),
            'total_statistical_value' => $statisticalValue ?? fake()->randomFloat(2, 10000, 1000000),
            'total_net_mass' => $netMass ?? fake()->randomFloat(3, 10, 5000),
        ]);
    }

    private function generateDeclarationNumber(DateTimeImmutable $date, IntrastatDirection $direction): string
    {
        $prefix = $direction === IntrastatDirection::ARRIVAL ? 'ARR' : 'DIS';
        $sequence = mb_str_pad((string) fake()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT);

        return 'INTRA-'.$date->format('Ym').'-'.$prefix.'-'.$sequence;
    }
}
