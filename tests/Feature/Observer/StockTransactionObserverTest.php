<?php

declare(strict_types=1);

use App\Enums\IntrastatDirection;
use App\Enums\StockTransactionType;
use App\Models\IntrastatDeclaration;
use App\Models\IntrastatLine;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Supplier;

use function Pest\Laravel\assertDatabaseHas;

it('creates intrastat line when stock transaction is created for EU order', function () {
    $supplier = Supplier::factory()->create([
        'eu_tax_number' => 'BE123456789',
        'headquarters' => [
            'street' => 'Test Street',
            'city' => 'Brussels',
            'country' => 'BE',
        ],
    ]);

    $product = Product::factory()->create([
        'cn_code' => '99887766',
        'weight' => 3.5,
    ]);

    $order = Order::factory()
        ->purchaseOrder()
        ->create([
            'supplier_id' => $supplier->id,
        ]);

    // Create inbound stock transaction linked to order
    StockTransaction::factory()->create([
        'type' => StockTransactionType::INBOUND,
        'product_id' => $product->id,
        'quantity' => 20,
        'unit_cost' => 50,
        'total_cost' => 1000,
        'reference_type' => Order::class,
        'reference_id' => $order->id,
    ]);

    expect(IntrastatDeclaration::count())->toBe(1);
    expect(IntrastatLine::count())->toBe(1);

    $declaration = IntrastatDeclaration::first();
    expect($declaration->direction)->toBe(IntrastatDirection::ARRIVAL);

    assertDatabaseHas('intrastat_lines', [
        'intrastat_declaration_id' => $declaration->id,
        'order_id' => $order->id,
        'product_id' => $product->id,
        'supplier_id' => $supplier->id,
        'cn_code' => '99887766',
        'quantity' => 20,
        'invoice_value' => 1000,
        'country_of_origin' => 'BE',
    ]);
});

it('does not create intrastat line for outbound stock transaction', function () {
    $product = Product::factory()->create();

    StockTransaction::factory()->create([
        'type' => StockTransactionType::OUTBOUND,
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    expect(IntrastatDeclaration::count())->toBe(0);
    expect(IntrastatLine::count())->toBe(0);
});

it('does not create intrastat line when supplier has no EU tax number', function () {
    $supplier = Supplier::factory()->create([
        'eu_tax_number' => null,
    ]);

    $product = Product::factory()->create();

    $order = Order::factory()
        ->purchaseOrder()
        ->create([
            'supplier_id' => $supplier->id,
        ]);

    StockTransaction::factory()->create([
        'type' => StockTransactionType::INBOUND,
        'product_id' => $product->id,
        'reference_type' => Order::class,
        'reference_id' => $order->id,
    ]);

    expect(IntrastatDeclaration::count())->toBe(0);
    expect(IntrastatLine::count())->toBe(0);
});

it('does not create intrastat line when transaction has no order reference', function () {
    $product = Product::factory()->create();

    StockTransaction::factory()->create([
        'type' => StockTransactionType::INBOUND,
        'product_id' => $product->id,
        'reference_type' => null,
        'reference_id' => null,
    ]);

    expect(IntrastatDeclaration::count())->toBe(0);
    expect(IntrastatLine::count())->toBe(0);
});

it('calculates net mass correctly based on product weight', function () {
    $supplier = Supplier::factory()->create([
        'eu_tax_number' => 'AT987654321',
        'headquarters' => ['country' => 'AT'],
    ]);

    $product = Product::factory()->create([
        'cn_code' => '12121212',
        'weight' => 7.25,
    ]);

    $order = Order::factory()
        ->purchaseOrder()
        ->create([
            'supplier_id' => $supplier->id,
        ]);

    StockTransaction::factory()->create([
        'type' => StockTransactionType::INBOUND,
        'product_id' => $product->id,
        'quantity' => 8,
        'unit_cost' => 25,
        'total_cost' => 200,
        'reference_type' => Order::class,
        'reference_id' => $order->id,
    ]);

    $line = IntrastatLine::first();
    expect($line->net_mass)->toBe('58.000'); // 7.25 * 8
});

it('creates declaration for current month when transaction is created', function () {
    $supplier = Supplier::factory()->create([
        'eu_tax_number' => 'PL123123123',
        'headquarters' => ['country' => 'PL'],
    ]);

    $product = Product::factory()->create(['cn_code' => '55555555']);

    $order = Order::factory()
        ->purchaseOrder()
        ->create([
            'supplier_id' => $supplier->id,
        ]);

    StockTransaction::factory()->create([
        'type' => StockTransactionType::INBOUND,
        'product_id' => $product->id,
        'reference_type' => Order::class,
        'reference_id' => $order->id,
    ]);

    $declaration = IntrastatDeclaration::first();
    expect($declaration->reference_year)->toBe((int) now()->format('Y'));
    expect($declaration->reference_month)->toBe((int) now()->format('m'));
});

it('reuses existing declaration for same month', function () {
    $supplier = Supplier::factory()->create([
        'eu_tax_number' => 'CZ111222333',
        'headquarters' => ['country' => 'CZ'],
    ]);

    $product = Product::factory()->create(['cn_code' => '44444444']);

    $order1 = Order::factory()
        ->purchaseOrder()
        ->create([
            'supplier_id' => $supplier->id,
        ]);

    $order2 = Order::factory()
        ->purchaseOrder()
        ->create([
            'supplier_id' => $supplier->id,
        ]);

    // First transaction
    StockTransaction::factory()->create([
        'type' => StockTransactionType::INBOUND,
        'product_id' => $product->id,
        'reference_type' => Order::class,
        'reference_id' => $order1->id,
    ]);

    expect(IntrastatDeclaration::count())->toBe(1);

    // Second transaction in same month
    StockTransaction::factory()->create([
        'type' => StockTransactionType::INBOUND,
        'product_id' => $product->id,
        'reference_type' => Order::class,
        'reference_id' => $order2->id,
    ]);

    // Should still be one declaration
    expect(IntrastatDeclaration::count())->toBe(1);
    // But two lines
    expect(IntrastatLine::count())->toBe(2);
});
