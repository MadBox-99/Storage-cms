<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CountryCode;
use App\Enums\IntrastatDeliveryTerms;
use App\Enums\IntrastatDirection;
use App\Enums\IntrastatStatus;
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
                'status' => IntrastatStatus::DRAFT,
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

    /**
     * Export declaration to KSH iFORM-compliant XML format for KSH-Elektra submission
     */
    public function exportToIFormXml(IntrastatDeclaration $declaration): string
    {
        // Create iFORM-compliant XML structure
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><form xmlns="http://iform-html.kdiv.hu/schemas/form"></form>');

        // Add keys (version information)
        $keys = $xml->addChild('keys');
        $this->addKey($keys, 'iformVersion', '1.13.7');

        // Add template keys (OSAP questionnaire identifiers)
        $templateKeys = $xml->addChild('templateKeys');
        $osapCode = $declaration->direction === IntrastatDirection::ARRIVAL ? '2012' : '2010';
        $this->addKey($templateKeys, 'OSAP', $osapCode);
        $this->addKey($templateKeys, 'EV', (string) $declaration->reference_year);
        $this->addKey($templateKeys, 'HO', (string) $declaration->reference_month);
        $this->addKey($templateKeys, 'VARIANT', '1');
        $this->addKey($templateKeys, 'MUTATION', '0');

        // Chapter 0: Metadata and contact information
        $chapter0 = $xml->addChild('chapter');
        $chapter0->addAttribute('s', 'P');
        $this->addData($chapter0, 'MHO', sprintf('%02d', $declaration->reference_month));
        $this->addData($chapter0, 'MEV', (string) $declaration->reference_year);
        $this->addData($chapter0, 'ADOSZAM', config('app.tax_number', '12345678-2-42'));

        // Chapter 1: Line items table and summary
        $chapter1 = $xml->addChild('chapter');
        $chapter1->addAttribute('s', 'P');

        // Add summary data
        $this->addData($chapter1, 'LAP_SUM', (string) $declaration->intrastatLines->count());
        $this->addData($chapter1, 'LAP_KGM_SUM', number_format((float) $declaration->total_net_mass, 3, '.', ''));

        // Add table with line items
        $table = $chapter1->addChild('table');
        $table->addAttribute('name', 'Termek');

        foreach ($declaration->intrastatLines as $index => $line) {
            $row = $table->addChild('row');

            // Line number
            $this->addData($row, 'T_SORSZ', (string) ($index + 1));

            // CN code
            $this->addData($row, 'TEKOD', $line->cn_code);

            // Transaction type (RTA for dispatch, FTA for arrival)
            $transactionField = $declaration->direction === IntrastatDirection::ARRIVAL ? 'FTA' : 'RTA';
            $this->addData($row, $transactionField, $line->transaction_type->value);

            // Country code
            $countryCode = $declaration->direction === IntrastatDirection::ARRIVAL
                ? $line->country_of_consignment
                : $line->country_of_destination;
            $this->addData($row, 'SZAORSZ', $countryCode);

            // Net mass
            $this->addData($row, 'KGM', number_format((float) $line->net_mass, 3, '.', ''));

            // Statistical value (SZAOSSZ for dispatch, STAERT for arrival)
            $valueField = $declaration->direction === IntrastatDirection::ARRIVAL ? 'STAERT' : 'SZAOSSZ';
            $this->addData($row, $valueField, (string) (int) $line->statistical_value);

            // Supplementary quantity if exists
            if ($line->supplementary_quantity) {
                $this->addData($row, 'KIEGME', number_format((float) $line->supplementary_quantity, 2, '.', ''));
                $this->addData($row, 'UKOD', '11'); // Unit code - should be mapped properly
            }

            // Transport mode
            $this->addData($row, 'SZALMOD', $line->transport_mode->value);

            // Delivery terms
            $this->addData($row, 'SZALFEL', $line->delivery_terms->value);

            // Country of origin for arrivals
            if ($declaration->direction === IntrastatDirection::ARRIVAL && $line->country_of_origin) {
                $this->addData($row, 'SZSZAORSZ', $line->country_of_origin);
            }
        }

        // Format XML with proper indentation
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }

    /**
     * Export declaration to simplified XML format (for documentation/internal use)
     */
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
        $prefix = "Sor {$lineNumber}: ";

        // CN code validation - KSH követelmény: kötelező 8 jegyű szám
        if (! $line->cn_code || mb_strlen($line->cn_code) !== 8 || ! ctype_digit($line->cn_code)) {
            $errors[] = $prefix.'KN kód kötelező, pontosan 8 számjegyből kell állnia';
        }

        // Net mass validation - KSH követelmény: minimum 0.001 kg
        if (! $line->net_mass || $line->net_mass < 0.001) {
            $errors[] = $prefix.'Nettó tömeg kötelező, minimum 0.001 kg';
        }

        // Invoice value validation - KSH követelmény: minimum 1 HUF
        if (! $line->invoice_value || $line->invoice_value < 1) {
            $errors[] = $prefix.'Számlaérték kötelező, minimum 1 HUF';
        }

        // Statistical value validation
        if (! $line->statistical_value || $line->statistical_value < 1) {
            $errors[] = $prefix.'Statisztikai érték kötelező, minimum 1 HUF';
        }

        // Country code validation - KSH követelmény: EU tagállamok, HU kivételével
        $euCountries = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'];

        if ($line->country_of_consignment && ! in_array($line->country_of_consignment, $euCountries, true)) {
            $errors[] = $prefix.'Feladás országa érvénytelen (csak EU tagállamok, HU kivételével)';
        }

        if ($line->country_of_destination && ! in_array($line->country_of_destination, $euCountries, true)) {
            $errors[] = $prefix.'Rendeltetési ország érvénytelen (csak EU tagállamok, HU kivételével)';
        }

        // Required fields - KSH követelmények
        if (! $line->transaction_type) {
            $errors[] = $prefix.'Ügylet jellege kötelező';
        }

        if (! $line->transport_mode) {
            $errors[] = $prefix.'Szállítási mód kötelező';
        }

        if (! $line->delivery_terms) {
            $errors[] = $prefix.'Szállítási feltétel kötelező (KSH követelmény)';
        }

        // Quantity validation
        if (! $line->quantity || $line->quantity <= 0) {
            $errors[] = $prefix.'Mennyiség kötelező és pozitív kell legyen';
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
                if (! $order->supplier || ! $order->supplier->is_eu_member || $order->supplier->country_code === CountryCode::HU) {
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

            $lineTotal = $orderLine->calculateSubtotal();

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
                'invoice_value' => $lineTotal,
                'statistical_value' => $lineTotal, // Simplified: same as invoice value
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

    private function addKey(SimpleXMLElement $parent, string $name, string $value): void
    {
        $key = $parent->addChild('key');
        $key->addChild('name', $name);
        $key->addChild('value', $value);
    }

    private function addData(SimpleXMLElement $parent, string $identifier, string $value): void
    {
        $data = $parent->addChild('data');
        $data->addAttribute('s', 'P');
        $data->addChild('identifier', $identifier);
        $data->addChild('value', $value);
    }
}
