<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use App\Models\Warehouse;
use App\Notifications\LowStockAlert;
use App\Notifications\OverstockAlert;
use Illuminate\Support\Facades\Notification;

uses()->group('database');

it('can set minimum and maximum stock levels for a product in a warehouse', function () {
    $product = Product::factory()->create([
        'min_stock' => 10,
        'max_stock' => 100,
        'reorder_point' => 20,
    ]);

    $warehouse = Warehouse::factory()->create();

    $stock = Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 50,
        'minimum_stock' => 15,
        'maximum_stock' => 150,
    ]);

    expect($stock->minimum_stock)->toBe(15);
    expect($stock->maximum_stock)->toBe(150);
    expect($stock->quantity)->toBe(50);
});

it('detects low stock condition', function () {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $stock = Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 5,
        'minimum_stock' => 10,
    ]);

    expect($stock->isLowStock())->toBeTrue();

    $stock->update(['quantity' => 15]);

    expect($stock->isLowStock())->toBeFalse();
});

it('detects reorder point reached for product', function () {
    $product = Product::factory()->create([
        'reorder_point' => 50,
    ]);

    $warehouse = Warehouse::factory()->create();

    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 30,
    ]);

    $product->refresh();

    expect($product->needsReorder())->toBeTrue();
    expect($product->getTotalStock())->toBe(30);
    expect($product->calculateReorderQuantity())->toBeGreaterThan(0);
});

it('sends low stock alert notification when stock drops below minimum', function () {
    Notification::fake();

    User::factory()->create(['is_super_admin' => true, 'email' => 'admin@example.com']);

    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $stock = Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 15,
        'minimum_stock' => 10,
    ]);

    $stock->update(['quantity' => 8]);

    Notification::assertSentTo(
        User::where('is_super_admin', true)->get(),
        LowStockAlert::class,
        function ($notification) use ($stock) {
            return $notification->stock->id === $stock->id;
        }
    );
});

it('sends overstock alert notification when stock exceeds maximum', function () {
    Notification::fake();

    User::factory()->create(['is_super_admin' => true, 'email' => 'admin@example.com']);

    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $stock = Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 80,
        'maximum_stock' => 100,
    ]);

    $stock->update(['quantity' => 120]);

    Notification::assertSentTo(
        User::where('is_super_admin', true)->get(),
        OverstockAlert::class,
        function ($notification) use ($stock) {
            return $notification->stock->id === $stock->id;
        }
    );
});

it('does not send alert when stock level is normal', function () {
    Notification::fake();

    User::factory()->create(['is_super_admin' => true]);

    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $stock = Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 50,
        'minimum_stock' => 10,
        'maximum_stock' => 100,
    ]);

    $stock->update(['quantity' => 60]);

    Notification::assertNothingSent();
});

it('calculates recommended reorder quantity correctly', function () {
    $product = Product::factory()->create([
        'max_stock' => 100,
        'reorder_point' => 20,
    ]);

    $warehouse = Warehouse::factory()->create();

    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 15,
    ]);

    $product->refresh();

    $reorderQty = $product->calculateReorderQuantity();

    expect($reorderQty)->toBe(85); // 100 - 15
    expect($product->needsReorder())->toBeTrue();
});
