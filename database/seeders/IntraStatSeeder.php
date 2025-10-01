<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\IntrastatDeliveryTerms;
use App\Enums\IntrastatDirection;
use App\Enums\IntrastatStatus;
use App\Enums\IntrastatTransactionType;
use App\Enums\IntrastatTransportMode;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Customer;
use App\Models\IntrastatDeclaration;
use App\Models\IntrastatLine;
use App\Models\Order;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

final class IntraStatSeeder extends Seeder
{
    public function run(): void
    {
        // EU beszállító létrehozása (Németország)
        $germanSupplier = Supplier::factory()->create([
            'company_name' => 'Deutsche Import GmbH',
            'email' => 'info@deutsche-import.de',
            'phone' => '+49 30 123456',
            'eu_tax_number' => 'DE123456789',
            'headquarters' => [
                'country' => 'DE',
                'city' => 'Berlin',
                'address' => 'Alexanderplatz 1',
                'zip_code' => '10178',
            ],
        ]);

        // EU vevő létrehozása (Ausztria)
        $austrianCustomer = Customer::factory()->create([
            'name' => 'Österreichische Handel AG',
            'email' => 'office@osterreich-handel.at',
            'phone' => '+43 1 9876543',

        ]);

        // Termékek létrehozása CN kóddal
        $product1 = Product::factory()->create([
            'name' => 'Precíziós csapágy',
            'sku' => 'PROD-BEARING-001',
            'cn_code' => '84821010', // KSH követelmény: 8 jegyű CN kód
            'country_of_origin' => 'DE',
            'price' => 45000,
            'weight' => 2.5, // kg
        ]);

        $product2 = Product::factory()->create([
            'name' => 'Ipari motor',
            'sku' => 'PROD-MOTOR-002',
            'cn_code' => '85015310', // Egyenáramú motor
            'country_of_origin' => 'DE',
            'price' => 125000,
            'weight' => 15.0, // kg
        ]);

        // === BESZÁLLÍTÁS (ARRIVAL) példa ===

        // Beszerzési rendelés létrehozása EU beszállítótól
        $purchaseOrder = Order::create([
            'order_number' => 'ORD-'.now()->format('Ymd').'-IMPORT',
            'type' => OrderType::PURCHASE,
            'supplier_id' => $germanSupplier->id,
            'status' => OrderStatus::DELIVERED,
            'order_date' => now()->subDays(10),
            'delivery_date' => now()->subDays(3),
            'total_amount' => 340000,
        ]);

        // Intrastat bevallás létrehozása - BESZÁLLÍTÁS
        $arrivalDeclaration = IntrastatDeclaration::create([
            'declaration_number' => 'INTRA-'.now()->format('Ym').'-ARR-001',
            'direction' => IntrastatDirection::ARRIVAL,
            'reference_year' => now()->year,
            'reference_month' => now()->month,
            'declaration_date' => now(),
            'status' => IntrastatStatus::DRAFT,
        ]);

        // Intrastat sorok a beszállításhoz
        IntrastatLine::factory()->create([
            'intrastat_declaration_id' => $arrivalDeclaration->id,
            'order_id' => $purchaseOrder->id,
            'product_id' => $product1->id,
            'supplier_id' => $germanSupplier->id,
            'cn_code' => $product1->cn_code,
            'quantity' => 10,
            'net_mass' => 25.0, // 10 db * 2.5 kg
            'invoice_value' => 450000, // 10 * 45000 Ft
            'statistical_value' => 450000,
            'country_of_origin' => 'DE',
            'country_of_consignment' => 'DE', // Feladó ország
            'transaction_type' => IntrastatTransactionType::OUTRIGHT_PURCHASE_SALE,
            'transport_mode' => IntrastatTransportMode::ROAD,
            'delivery_terms' => IntrastatDeliveryTerms::CIF,
            'description' => 'Precíziós csapágyak beszállítása',
        ]);

        IntrastatLine::create([
            'intrastat_declaration_id' => $arrivalDeclaration->id,
            'order_id' => $purchaseOrder->id,
            'product_id' => $product2->id,
            'supplier_id' => $germanSupplier->id,
            'cn_code' => $product2->cn_code,
            'quantity' => 5,
            'net_mass' => 75.0, // 5 db * 15 kg
            'invoice_value' => 625000, // 5 * 125000 Ft
            'statistical_value' => 625000,
            'country_of_origin' => 'DE',
            'country_of_consignment' => 'DE',
            'transaction_type' => IntrastatTransactionType::OUTRIGHT_PURCHASE_SALE,
            'transport_mode' => IntrastatTransportMode::ROAD,
            'delivery_terms' => IntrastatDeliveryTerms::CIF,
            'description' => 'Ipari motorok beszállítása',
        ]);

        // Összegek kalkulálása
        $arrivalDeclaration->calculateTotals();

        // === KISZÁLLÍTÁS (DISPATCH) példa ===

        // Értékesítési rendelés létrehozása EU vevőnek
        $salesOrder = Order::create([
            'order_number' => 'ORD-'.now()->format('Ymd').'-EXPORT',
            'type' => OrderType::SALE,
            'customer_id' => $austrianCustomer->id,
            'status' => OrderStatus::DELIVERED,
            'order_date' => now()->subDays(5),
            'delivery_date' => now()->subDays(1),
            'total_amount' => 560000,

        ]);

        // Intrastat bevallás létrehozása - KISZÁLLÍTÁS
        $dispatchDeclaration = IntrastatDeclaration::create([
            'declaration_number' => 'INTRA-'.now()->format('Ym').'-DIS-001',
            'direction' => IntrastatDirection::DISPATCH,
            'reference_year' => now()->year,
            'reference_month' => now()->month,
            'declaration_date' => now(),
            'status' => IntrastatStatus::READY,
            'submitted_at' => now(),
            'submitted_by' => 'Admin User',
        ]);

        // Intrastat sorok a kiszállításhoz
        IntrastatLine::create([
            'intrastat_declaration_id' => $dispatchDeclaration->id,
            'order_id' => $salesOrder->id,
            'product_id' => $product1->id,
            'supplier_id' => null, // Értékesítésnél nincs beszállító
            'cn_code' => $product1->cn_code,
            'quantity' => 8,
            'net_mass' => 20.0, // 8 db * 2.5 kg
            'invoice_value' => 360000,
            'statistical_value' => 360000,
            'country_of_origin' => 'HU', // Magyar termék
            'country_of_destination' => 'AT', // Rendeltetési ország
            'transaction_type' => IntrastatTransactionType::OUTRIGHT_PURCHASE_SALE,
            'transport_mode' => IntrastatTransportMode::ROAD,
            'delivery_terms' => IntrastatDeliveryTerms::FOB,
            'description' => 'Csapágyak értékesítése Ausztriába',
        ]);

        // Összegek kalkulálása
        $dispatchDeclaration->calculateTotals();

        $this->command->info('Intrastat példa adatok sikeresen létrehozva:');
        $this->command->info('- 1 EU beszállító (Németország)');
        $this->command->info('- 1 EU vevő (Ausztria)');
        $this->command->info('- 2 termék CN kóddal');
        $this->command->info('- 1 BESZÁLLÍTÁS bevallás (DRAFT) - 2 sorral');
        $this->command->info('- 1 KISZÁLLÍTÁS bevallás (READY) - 1 sorral');
    }
}
