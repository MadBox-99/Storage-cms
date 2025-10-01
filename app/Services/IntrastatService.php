<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\IntrastatDeliveryTerms;
use App\Enums\IntrastatDirection;
use App\Enums\IntrastatTransactionType;
use App\Enums\IntrastatTransportMode;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\IntrastatDeclaration;
use App\Models\IntrastatLine;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

final class IntrastatService
{
    public function generateDeclarationForPeriod(
        int $year,
        int $month,
        IntrastatDirection $direction
    ): IntrastatDeclaration {
        return DB::transaction(function () use ($year, $month, $direction): IntrastatDeclaration {
            // Create declaration
            $declaration = IntrastatDeclaration::create([
                'declaration_number' => $this->generateDeclarationNumber($year, $month, $direction),
                'direction' => $direction,
                'reference_year' => $year,
                'reference_month' => $month,
                'declaration_date' => now(),
                'status' => 'DRAFT',
            ]);

            // Get relevant orders for the period
            $orders = $this->getOrdersForPeriod($year, $month, $direction);

            // Generate lines from orders
            foreach ($orders as $order) {
                $this->generateLinesFromOrder($declaration, $order, $direction);
            }

            // Calculate totals
            $declaration->calculateTotals();

            return $declaration;
        });
    }

    public function exportToXml(IntrastatDeclaration $declaration): string
    {
        // TODO: Implement KSH specific XML format
        // This would require the official KSH XML schema
        return '';
    }

    private function generateDeclarationNumber(
        int $year,
        int $month,
        IntrastatDirection $direction
    ): string {
        $monthStr = mb_str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $dirCode = $direction === IntrastatDirection::ARRIVAL ? 'A' : 'D';

        return "INTRASTAT-{$year}{$monthStr}-{$dirCode}";
    }

    private function getOrdersForPeriod(
        int $year,
        int $month,
        IntrastatDirection $direction
    ) {
        $orderType = $direction === IntrastatDirection::ARRIVAL
            ? OrderType::PURCHASE
            : OrderType::SALE;

        return Order::query()
            ->with(['orderLines.product', 'supplier', 'customer'])
            ->where('type', $orderType)
            ->whereIn('status', [OrderStatus::COMPLETED, OrderStatus::CONFIRMED])
            ->whereYear('order_date', $year)
            ->whereMonth('order_date', $month)
            ->get();
    }

    private function generateLinesFromOrder(
        IntrastatDeclaration $declaration,
        Order $order,
        IntrastatDirection $direction
    ): void {
        foreach ($order->orderLines as $orderLine) {
            $product = $orderLine->product;

            // Skip if product doesn't have CN code or is not from/to EU
            if (! $product || ! $product->cn_code) {
                continue;
            }

            // For purchases (arrival), check if supplier is from EU
            if ($direction === IntrastatDirection::ARRIVAL) {
                if (! $order->supplier || ! $order->supplier->is_eu_member || $order->supplier->country_code === 'HU') {
                    continue;
                }
            }

            // For sales (dispatch), check if customer is from EU
            if ($direction === IntrastatDirection::DISPATCH) {
                if (! $order->customer) {
                    continue;
                }
                // Would need customer country check here if we had that field
            }

            $netMass = $product->net_weight_kg
                ? $product->net_weight_kg * $orderLine->quantity
                : 0;

            IntrastatLine::create([
                'intrastat_declaration_id' => $declaration->id,
                'order_id' => $order->id,
                'product_id' => $product->id,
                'supplier_id' => $order->supplier_id,
                'cn_code' => $product->cn_code,
                'quantity' => $orderLine->quantity,
                'net_mass' => $netMass,
                'supplementary_unit' => $product->supplementary_unit,
                'supplementary_quantity' => $product->supplementary_unit ? $orderLine->quantity : null,
                'invoice_value' => $orderLine->line_total,
                'statistical_value' => $orderLine->line_total, // Simplified: same as invoice value
                'country_of_origin' => $product->country_of_origin ?? 'HU',
                'country_of_consignment' => $direction === IntrastatDirection::ARRIVAL
                    ? ($order->supplier?->country_code ?? 'HU')
                    : 'HU',
                'country_of_destination' => $direction === IntrastatDirection::DISPATCH
                    ? ($order->supplier?->country_code ?? 'HU')
                    : 'HU',
                'transaction_type' => IntrastatTransactionType::OUTRIGHT_PURCHASE_SALE,
                'transport_mode' => IntrastatTransportMode::ROAD,
                'delivery_terms' => IntrastatDeliveryTerms::EXW,
                'description' => $product->name,
            ]);
        }
    }
}
