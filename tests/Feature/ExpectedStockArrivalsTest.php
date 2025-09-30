<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Warehouse;

uses()->group('stock-management');

test('can retrieve expected arrivals for product', function (): void {
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();

    $order = Order::factory()->create([
        'type' => OrderType::PURCHASE,
        'status' => OrderStatus::CONFIRMED,
        'delivery_date' => now()->addDays(5),
        'supplier_id' => $supplier->id,
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 50,
    ]);

    $expectedArrivals = $product->getExpectedArrivals();

    expect($expectedArrivals)
        ->toHaveCount(1)
        ->first()->id->toBe($order->id);
});

test('does not include cancelled or delivered orders in expected arrivals', function (): void {
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();

    Order::factory()->create([
        'type' => OrderType::PURCHASE,
        'status' => OrderStatus::CANCELLED,
        'delivery_date' => now()->addDays(5),
        'supplier_id' => $supplier->id,
    ]);

    Order::factory()->create([
        'type' => OrderType::PURCHASE,
        'status' => OrderStatus::DELIVERED,
        'delivery_date' => now()->addDays(5),
        'supplier_id' => $supplier->id,
    ]);

    $expectedArrivals = $product->getExpectedArrivals();

    expect($expectedArrivals)->toHaveCount(0);
});

test('calculates total expected quantity correctly', function (): void {
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();

    $order1 = Order::factory()->create([
        'type' => OrderType::PURCHASE,
        'status' => OrderStatus::CONFIRMED,
        'delivery_date' => now()->addDays(3),
        'supplier_id' => $supplier->id,
    ]);

    $order2 = Order::factory()->create([
        'type' => OrderType::PURCHASE,
        'status' => OrderStatus::PROCESSING,
        'delivery_date' => now()->addDays(7),
        'supplier_id' => $supplier->id,
    ]);

    OrderLine::factory()->create([
        'order_id' => $order1->id,
        'product_id' => $product->id,
        'quantity' => 50,
    ]);

    OrderLine::factory()->create([
        'order_id' => $order2->id,
        'product_id' => $product->id,
        'quantity' => 75,
    ]);

    expect($product->getTotalExpectedQuantity())->toBe(125);
});

test('stock can be reserved', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    $stock = Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 100,
        'reserved_quantity' => 0,
    ]);

    $result = $stock->reserve(30);

    $stock->refresh();

    expect($result)->toBeTrue()
        ->and($stock->reserved_quantity)->toBe(30)
        ->and($stock->getAvailableQuantity())->toBe(70);
});

test('cannot reserve more than available quantity', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    $stock = Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 50,
        'reserved_quantity' => 30,
    ]);

    $result = $stock->reserve(30);

    expect($result)->toBeFalse();
});

test('can release reserved stock', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    $stock = Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 100,
        'reserved_quantity' => 40,
    ]);

    $stock->release(20);

    $stock->refresh();

    expect($stock->reserved_quantity)->toBe(20)
        ->and($stock->getAvailableQuantity())->toBe(80);
});

test('warehouse can be marked as consignment', function (): void {
    $supplier = Supplier::factory()->create();

    $warehouse = Warehouse::factory()->create([
        'is_consignment' => true,
        'owner_supplier_id' => $supplier->id,
    ]);

    expect($warehouse->isConsignment())->toBeTrue()
        ->and($warehouse->owner_supplier_id)->toBe($supplier->id)
        ->and($warehouse->ownerSupplier)->not->toBeNull();
});

test('expected arrivals are ordered by delivery date', function (): void {
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();

    $order1 = Order::factory()->create([
        'type' => OrderType::PURCHASE,
        'status' => OrderStatus::CONFIRMED,
        'delivery_date' => now()->addDays(10),
        'supplier_id' => $supplier->id,
    ]);

    $order2 = Order::factory()->create([
        'type' => OrderType::PURCHASE,
        'status' => OrderStatus::PROCESSING,
        'delivery_date' => now()->addDays(3),
        'supplier_id' => $supplier->id,
    ]);

    OrderLine::factory()->create([
        'order_id' => $order1->id,
        'product_id' => $product->id,
        'quantity' => 50,
    ]);

    OrderLine::factory()->create([
        'order_id' => $order2->id,
        'product_id' => $product->id,
        'quantity' => 30,
    ]);

    $expectedArrivals = $product->getExpectedArrivals();

    expect($expectedArrivals)
        ->toHaveCount(2)
        ->first()->id->toBe($order2->id)
        ->and($expectedArrivals->last()->id)->toBe($order1->id);
});
