<?php

declare(strict_types=1);

use App\Enums\IntrastatDirection;
use App\Enums\OrderStatus;
use App\Models\IntrastatDeclaration;
use App\Models\IntrastatLine;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\Supplier;

use function Pest\Laravel\assertDatabaseHas;

it('creates intrastat lines when purchase order is delivered with EU supplier', function () {
    $supplier = Supplier::factory()->create([
        'eu_tax_number' => 'DE123456789',
        'headquarters' => [
            'street' => 'Test Street 1',
            'city' => 'Berlin',
            'country' => 'DE',
        ],
    ]);

    $product = Product::factory()->create([
        'cn_code' => '12345678',
        'weight' => 5.5,
    ]);

    $order = Order::factory()
        ->purchaseOrder()
        ->create([
            'supplier_id' => $supplier->id,
            'delivery_date' => now(),
            'status' => OrderStatus::CONFIRMED,
        ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_price' => 100,
    ]);

    // Update order status to DELIVERED to trigger observer
    $order->update(['status' => OrderStatus::DELIVERED]);

    // Assert Intrastat declaration was created
    expect(IntrastatDeclaration::count())->toBe(1);

    $declaration = IntrastatDeclaration::first();
    expect($declaration->direction)->toBe(IntrastatDirection::ARRIVAL);
    expect($declaration->status)->toBe('DRAFT');

    // Assert Intrastat line was created
    assertDatabaseHas('intrastat_lines', [
        'intrastat_declaration_id' => $declaration->id,
        'order_id' => $order->id,
        'product_id' => $product->id,
        'supplier_id' => $supplier->id,
        'cn_code' => '12345678',
        'quantity' => 10,
        'country_of_origin' => 'DE',
    ]);
});

it('does not create intrastat lines when supplier has no EU tax number', function () {
    $supplier = Supplier::factory()->create([
        'eu_tax_number' => null,
    ]);

    $order = Order::factory()
        ->purchaseOrder()
        ->create([
            'supplier_id' => $supplier->id,
            'status' => OrderStatus::CONFIRMED,
        ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
    ]);

    $order->update(['status' => OrderStatus::DELIVERED]);

    expect(IntrastatDeclaration::count())->toBe(0);
    expect(IntrastatLine::count())->toBe(0);
});

it('creates intrastat lines for sales order with EU customer', function () {
    $order = Order::factory()
        ->salesOrder()
        ->create([
            'shipping_address' => [
                'street' => 'Test Street',
                'city' => 'Paris',
                'country' => 'FR',
            ],
            'status' => OrderStatus::CONFIRMED,
        ]);

    $product = Product::factory()->create([
        'cn_code' => '87654321',
        'weight' => 2.5,
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'unit_price' => 200,
    ]);

    $order->update(['status' => OrderStatus::DELIVERED]);

    expect(IntrastatDeclaration::count())->toBe(1);

    $declaration = IntrastatDeclaration::first();
    expect($declaration->direction)->toBe(IntrastatDirection::DISPATCH);

    assertDatabaseHas('intrastat_lines', [
        'intrastat_declaration_id' => $declaration->id,
        'order_id' => $order->id,
        'product_id' => $product->id,
        'cn_code' => '87654321',
        'quantity' => 5,
        'country_of_destination' => 'FR',
    ]);
});

it('does not create intrastat lines for non-EU customer', function () {
    $order = Order::factory()
        ->salesOrder()
        ->create([
            'shipping_address' => [
                'street' => 'Test Street',
                'city' => 'New York',
                'country' => 'US',
            ],
            'status' => OrderStatus::CONFIRMED,
        ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
    ]);

    $order->update(['status' => OrderStatus::DELIVERED]);

    expect(IntrastatDeclaration::count())->toBe(0);
    expect(IntrastatLine::count())->toBe(0);
});

it('creates declaration with correct month and year', function () {
    $supplier = Supplier::factory()->create([
        'eu_tax_number' => 'IT123456789',
        'headquarters' => ['country' => 'IT'],
    ]);

    $deliveryDate = now()->setDate(2025, 3, 15);

    $order = Order::factory()
        ->purchaseOrder()
        ->create([
            'supplier_id' => $supplier->id,
            'delivery_date' => $deliveryDate,
            'status' => OrderStatus::CONFIRMED,
        ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
    ]);

    $order->update(['status' => OrderStatus::DELIVERED]);

    $declaration = IntrastatDeclaration::first();
    expect($declaration->reference_year)->toBe(2025);
    expect($declaration->reference_month)->toBe(3);
    expect($declaration->declaration_number)->toBe('ARRIVAL-2025-03');
});

it('reuses existing declaration for same month and direction', function () {
    $supplier = Supplier::factory()->create([
        'eu_tax_number' => 'ES123456789',
        'headquarters' => ['country' => 'ES'],
    ]);

    $product = Product::factory()->create(['cn_code' => '12345678']);

    $order1 = Order::factory()
        ->purchaseOrder()
        ->create([
            'supplier_id' => $supplier->id,
            'delivery_date' => now(),
            'status' => OrderStatus::CONFIRMED,
        ]);

    OrderLine::factory()->create([
        'order_id' => $order1->id,
        'product_id' => $product->id,
    ]);

    $order1->update(['status' => OrderStatus::DELIVERED]);

    expect(IntrastatDeclaration::count())->toBe(1);

    // Create another order in same month
    $order2 = Order::factory()
        ->purchaseOrder()
        ->create([
            'supplier_id' => $supplier->id,
            'delivery_date' => now(),
            'status' => OrderStatus::CONFIRMED,
        ]);

    OrderLine::factory()->create([
        'order_id' => $order2->id,
        'product_id' => $product->id,
    ]);

    $order2->update(['status' => OrderStatus::DELIVERED]);

    // Should still be only one declaration
    expect(IntrastatDeclaration::count())->toBe(1);
    // But two intrastat lines
    expect(IntrastatLine::count())->toBe(2);
});

it('calculates totals correctly when lines are added', function () {
    $supplier = Supplier::factory()->create([
        'eu_tax_number' => 'NL123456789',
        'headquarters' => ['country' => 'NL'],
    ]);

    $order = Order::factory()
        ->purchaseOrder()
        ->create([
            'supplier_id' => $supplier->id,
            'status' => OrderStatus::CONFIRMED,
        ]);

    $product = Product::factory()->create([
        'weight' => 10,
        'cn_code' => '11111111',
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'unit_price' => 100,
    ]);

    $order->update(['status' => OrderStatus::DELIVERED]);

    $declaration = IntrastatDeclaration::first();
    expect($declaration->total_invoice_value)->toBe('500.00');
    expect($declaration->total_net_mass)->toBe('50.000');
});
