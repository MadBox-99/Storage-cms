<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\IntrastatDeliveryTerms;
use App\Enums\IntrastatDirection;
use App\Enums\IntrastatTransactionType;
use App\Enums\IntrastatTransportMode;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\IntrastatDeclaration;
use App\Models\IntrastatLine;
use App\Models\Order;
use DateTimeInterface;

final class OrderObserver
{
    public function updated(Order $order): void
    {
        // Only create Intrastat lines when order is completed/delivered
        if ($order->isDirty('status') && $order->status === OrderStatus::DELIVERED) {
            $this->createIntrastatLines($order);
        }
    }

    private function createIntrastatLines(Order $order): void
    {
        // Only process orders with EU suppliers/customers
        if ($order->type === OrderType::PURCHASE && $order->supplier) {
            $this->createInboundIntrastatLines($order);
        } elseif ($order->type === OrderType::SALES && $order->customer) {
            $this->createOutboundIntrastatLines($order);
        }
    }

    private function createInboundIntrastatLines(Order $order): void
    {
        // Check if supplier has EU tax number (indicating EU supplier)
        if (! $order->supplier->eu_tax_number) {
            return;
        }

        $declaration = $this->getOrCreateDeclaration(IntrastatDirection::ARRIVAL, $order->delivery_date);

        foreach ($order->orderLines as $orderLine) {
            if (! $orderLine->product) {
                continue;
            }

            // Skip products without CN code - required by KSH
            if (! $orderLine->product->cn_code) {
                continue;
            }

            IntrastatLine::create([
                'intrastat_declaration_id' => $declaration->id,
                'order_id' => $order->id,
                'product_id' => $orderLine->product_id,
                'supplier_id' => $order->supplier_id,
                'cn_code' => $orderLine->product->cn_code,
                'quantity' => $orderLine->quantity,
                'net_mass' => ($orderLine->product->weight ?? 0) * $orderLine->quantity,
                'invoice_value' => $orderLine->quantity * $orderLine->unit_price,
                'statistical_value' => $orderLine->quantity * $orderLine->unit_price,
                'country_of_origin' => $this->extractCountryCode($order->supplier->headquarters),
                'country_of_consignment' => $this->extractCountryCode($order->supplier->headquarters) ?? 'HU',
                'country_of_destination' => 'HU',
                'transaction_type' => IntrastatTransactionType::OUTRIGHT_PURCHASE_SALE,
                'transport_mode' => IntrastatTransportMode::ROAD,
                'delivery_terms' => IntrastatDeliveryTerms::EXW,
                'description' => $orderLine->product->name,
            ]);
        }

        $declaration->calculateTotals();
    }

    private function createOutboundIntrastatLines(Order $order): void
    {
        // Check if customer has EU address (indicating EU customer)
        $customerCountry = $this->extractCountryCode($order->shipping_address);

        if (! $customerCountry || ! $this->isEuCountry($customerCountry)) {
            return;
        }

        $declaration = $this->getOrCreateDeclaration(IntrastatDirection::DISPATCH, $order->delivery_date);

        foreach ($order->orderLines as $orderLine) {
            if (! $orderLine->product) {
                continue;
            }

            // Skip products without CN code - required by KSH
            if (! $orderLine->product->cn_code) {
                continue;
            }

            IntrastatLine::create([
                'intrastat_declaration_id' => $declaration->id,
                'order_id' => $order->id,
                'product_id' => $orderLine->product_id,
                'cn_code' => $orderLine->product->cn_code,
                'quantity' => $orderLine->quantity,
                'net_mass' => ($orderLine->product->weight ?? 0) * $orderLine->quantity,
                'invoice_value' => $orderLine->quantity * $orderLine->unit_price,
                'statistical_value' => $orderLine->quantity * $orderLine->unit_price,
                'country_of_consignment' => 'HU',
                'country_of_destination' => $customerCountry,
                'transaction_type' => IntrastatTransactionType::OUTRIGHT_PURCHASE_SALE,
                'transport_mode' => IntrastatTransportMode::ROAD,
                'delivery_terms' => IntrastatDeliveryTerms::EXW,
                'description' => $orderLine->product->name,
            ]);
        }

        $declaration->calculateTotals();
    }

    private function getOrCreateDeclaration(IntrastatDirection $direction, ?DateTimeInterface $date): IntrastatDeclaration
    {
        $date = $date ?? now();
        $year = $date->format('Y');
        $month = $date->format('m');

        return IntrastatDeclaration::firstOrCreate(
            [
                'direction' => $direction,
                'reference_year' => $year,
                'reference_month' => $month,
            ],
            [
                'declaration_number' => sprintf('%s-%s-%s', $direction->value, $year, $month),
                'declaration_date' => now(),
                'status' => 'DRAFT',
            ]
        );
    }

    private function extractCountryCode(array|string|null $address): ?string
    {
        if (is_array($address) && isset($address['country'])) {
            return $address['country'];
        }

        return null;
    }

    private function isEuCountry(string $countryCode): bool
    {
        $euCountries = [
            'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
            'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
            'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE',
        ];

        return in_array(mb_strtoupper($countryCode), $euCountries, true);
    }
}
