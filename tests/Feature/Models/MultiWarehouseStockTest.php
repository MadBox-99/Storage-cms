<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;

use function Pest\Laravel\assertDatabaseHas;

uses()->group('database');

it('can track product stock across multiple warehouses', function () {
    $product = Product::factory()->create(['sku' => 'TEST-001', 'name' => 'Test Product']);
    $warehouseA = Warehouse::factory()->create(['name' => 'Warehouse A', 'code' => 'WH-A']);
    $warehouseB = Warehouse::factory()->create(['name' => 'Warehouse B', 'code' => 'WH-B']);
    $warehouseC = Warehouse::factory()->create(['name' => 'Warehouse C', 'code' => 'WH-C']);

    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouseA->id,
        'quantity' => 100,
    ]);

    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouseB->id,
        'quantity' => 50,
    ]);

    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouseC->id,
        'quantity' => 25,
    ]);

    $product->refresh();

    expect($product->stocks)->toHaveCount(3);
    expect($product->getTotalStock())->toBe(175);

    assertDatabaseHas('stocks', [
        'product_id' => $product->id,
        'warehouse_id' => $warehouseA->id,
        'quantity' => 100,
    ]);

    assertDatabaseHas('stocks', [
        'product_id' => $product->id,
        'warehouse_id' => $warehouseB->id,
        'quantity' => 50,
    ]);

    assertDatabaseHas('stocks', [
        'product_id' => $product->id,
        'warehouse_id' => $warehouseC->id,
        'quantity' => 25,
    ]);
});

it('can view stock by warehouse for a specific product', function () {
    $product = Product::factory()->create(['sku' => 'TEST-002', 'name' => 'Another Product']);
    $warehouse1 = Warehouse::factory()->create(['name' => 'Main Warehouse', 'code' => 'MAIN']);
    $warehouse2 = Warehouse::factory()->create(['name' => 'Secondary Warehouse', 'code' => 'SEC']);

    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse1->id,
        'quantity' => 200,
    ]);

    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse2->id,
        'quantity' => 75,
    ]);

    $stockInWarehouse1 = $warehouse1->findProduct($product);
    $stockInWarehouse2 = $warehouse2->findProduct($product);

    expect($stockInWarehouse1)->not->toBeNull();
    expect($stockInWarehouse1->quantity)->toBe(200);

    expect($stockInWarehouse2)->not->toBeNull();
    expect($stockInWarehouse2->quantity)->toBe(75);
});

it('enforces unique product-warehouse constraint', function () {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();

    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 100,
    ]);

    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 50,
    ]);
})->throws(Exception::class);

it('can list all warehouses with stock for a product', function () {
    $product = Product::factory()->create(['sku' => 'TEST-003']);
    $warehouses = Warehouse::factory()->count(4)->create();

    foreach ($warehouses as $index => $warehouse) {
        Stock::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => ($index + 1) * 10,
        ]);
    }

    $product->load('stocks.warehouse');

    expect($product->stocks)->toHaveCount(4);

    $warehouseNames = $product->stocks->pluck('warehouse.name')->toArray();
    expect($warehouseNames)->toHaveCount(4);

    $totalStock = $product->stocks->sum('quantity');
    expect($totalStock)->toBe(100); // 10 + 20 + 30 + 40
});
