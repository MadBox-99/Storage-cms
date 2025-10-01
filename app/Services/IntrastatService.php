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
use DOMDocument;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

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
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><INTRASTAT></INTRASTAT>');

        // Header section
        $header = $xml->addChild('HEADER');
        $header->addChild('PSI_ID', config('app.tax_number', '12345678-2-42'));
        $header->addChild('REFERENCE_PERIOD', sprintf(
            '%d%02d',
            $declaration->reference_year,
            $declaration->reference_month
        ));
        $header->addChild('FLOW_CODE', $declaration->direction === IntrastatDirection::ARRIVAL ? 'A' : 'D');
        $header->addChild('DECLARATION_DATE', $declaration->declaration_date->format('Y-m-d'));
        $header->addChild('CURRENCY_CODE', 'HUF');

        // Items section
        $items = $xml->addChild('ITEMS');
        $lineNumber = 1;

        foreach ($declaration->intrastatLines as $line) {
            $item = $items->addChild('ITEM');
            $item->addChild('LINE_NUMBER', (string) $lineNumber++);
            $item->addChild('CN_CODE', $line->cn_code);

            // Country code based on direction
            $countryCode = $declaration->direction === IntrastatDirection::ARRIVAL
                ? $line->country_of_consignment
                : $line->country_of_destination;
            $item->addChild('COUNTRY_CODE', $countryCode);

            // Transaction details
            $item->addChild('NATURE_OF_TRANSACTION', $line->transaction_type->value);
            $item->addChild('MODE_OF_TRANSPORT', $line->transport_mode->value);
            $item->addChild('DELIVERY_TERMS', $line->delivery_terms->value);

            // Values
            $item->addChild('STATISTICAL_VALUE', (string) (int) $line->statistical_value);
            $item->addChild('NET_MASS', number_format((float) $line->net_mass, 3, '.', ''));

            // Supplementary unit if exists
            if ($line->supplementary_unit && $line->supplementary_quantity) {
                $item->addChild('SUPPLEMENTARY_UNIT', $line->supplementary_unit);
                $item->addChild('SUPPLEMENTARY_QUANTITY', number_format((float) $line->supplementary_quantity, 2, '.', ''));
            }

            // Country of origin (only for arrivals)
            if ($declaration->direction === IntrastatDirection::ARRIVAL && $line->country_of_origin) {
                $item->addChild('COUNTRY_OF_ORIGIN', $line->country_of_origin);
            }
        }

        // Summary section
        $summary = $xml->addChild('SUMMARY');
        $summary->addChild('TOTAL_LINES', (string) $declaration->intrastatLines->count());
        $summary->addChild('TOTAL_STATISTICAL_VALUE', (string) (int) $declaration->total_statistical_value);
        $summary->addChild('TOTAL_NET_MASS', number_format((float) $declaration->total_net_mass, 3, '.', ''));

        // Format XML with proper indentation
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }

    public function validateDeclaration(IntrastatDeclaration $declaration): array
    {
        $errors = [];

        // Check if declaration has lines
        if ($declaration->intrastatLines()->count() === 0) {
            $errors[] = 'Declaration must have at least one line';
        }

        // Validate each line
        foreach ($declaration->intrastatLines as $index => $line) {
            $lineErrors = $this->validateLine($line, $index + 1);
            $errors = array_merge($errors, $lineErrors);
        }

        return $errors;
    }

    private function validateLine(IntrastatLine $line, int $lineNumber): array
    {
        $errors = [];
        $prefix = "Line {$lineNumber}: ";

        // CN code validation
        if (! $line->cn_code || mb_strlen($line->cn_code) !== 8 || ! ctype_digit($line->cn_code)) {
            $errors[] = $prefix.'CN code must be exactly 8 digits';
        }

        // Net mass validation
        if (! $line->net_mass || $line->net_mass < 0.001) {
            $errors[] = $prefix.'Net mass must be at least 0.001 kg';
        }

        // Invoice value validation
        if (! $line->invoice_value || $line->invoice_value < 1) {
            $errors[] = $prefix.'Invoice value must be at least 1 HUF';
        }

        // Country code validation (EU members only, excluding HU)
        $euCountries = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'];

        if ($line->country_of_consignment && ! in_array($line->country_of_consignment, $euCountries, true)) {
            $errors[] = $prefix.'Country of consignment must be an EU member state (excluding HU)';
        }

        if ($line->country_of_destination && ! in_array($line->country_of_destination, $euCountries, true)) {
            $errors[] = $prefix.'Country of destination must be an EU member state (excluding HU)';
        }

        // Required fields
        if (! $line->transaction_type) {
            $errors[] = $prefix.'Transaction type is required';
        }

        if (! $line->transport_mode) {
            $errors[] = $prefix.'Transport mode is required';
        }

        if (! $line->delivery_terms) {
            $errors[] = $prefix.'Delivery terms are required';
        }

        return $errors;
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
