<?php

declare(strict_types=1);

use App\Enums\InventoryValuationMethod;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Services\Inventory\InventoryValuationService;

uses()->group('database');

beforeEach(function () {
    $this->service = app(InventoryValuationService::class);
});

it('calculates FIFO valuation correctly', function () {
    $warehouse = Warehouse::factory()->create(['valuation_method' => InventoryValuationMethod::FIFO]);
    $product = Product::factory()->create();
    $stock = Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 0,
    ]);

    // Receive 100 units @ $10
    $this->service->recordStockIn($stock, 100, 10.00);
    // Receive 50 units @ $12
    $this->service->recordStockIn($stock, 50, 12.00);

    $stock->refresh();
    expect($stock->quantity)->toBe(150);

    // Value should be: (100 * 10) + (50 * 12) = 1600
    $value = $this->service->calculateStockValue($stock);
    expect($value)->toBe(1600.0);

    // Issue 120 units (should use first 100 @ $10, then 20 @ $12)
    $this->service->recordStockOut($stock, 120);

    $stock->refresh();
    expect($stock->quantity)->toBe(30);

    // Remaining value should be: 30 * 12 = 360
    $value = $this->service->calculateStockValue($stock);
    expect($value)->toBe(360.0);
});

it('calculates LIFO valuation correctly', function () {
    $warehouse = Warehouse::factory()->create(['valuation_method' => InventoryValuationMethod::LIFO]);
    $product = Product::factory()->create();
    $stock = Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 0,
    ]);

    // Receive 100 units @ $10
    $this->service->recordStockIn($stock, 100, 10.00);
    // Receive 50 units @ $12
    $this->service->recordStockIn($stock, 50, 12.00);

    $stock->refresh();
    expect($stock->quantity)->toBe(150);

    // Value should be: (100 * 10) + (50 * 12) = 1600
    $value = $this->service->calculateStockValue($stock);
    expect($value)->toBe(1600.0);

    // Issue 120 units (should use latest 50 @ $12, then 70 @ $10)
    $this->service->recordStockOut($stock, 120);

    $stock->refresh();
    expect($stock->quantity)->toBe(30);

    // Remaining value should be: 30 * 10 = 300
    $value = $this->service->calculateStockValue($stock);
    expect($value)->toBe(300.0);
});

it('calculates weighted average valuation correctly', function () {
    $warehouse = Warehouse::factory()->create(['valuation_method' => InventoryValuationMethod::WEIGHTED_AVERAGE]);
    $product = Product::factory()->create();
    $stock = Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 0,
    ]);

    // Receive 100 units @ $10
    $this->service->recordStockIn($stock, 100, 10.00);
    // Receive 50 units @ $12
    $this->service->recordStockIn($stock, 50, 12.00);

    $stock->refresh();
    expect($stock->quantity)->toBe(150);

    // Weighted average = (100*10 + 50*12) / 150 = 1600 / 150 = 10.6667
    // Total value = 150 * 10.6667 = 1600
    $value = $this->service->calculateStockValue($stock);
    expect(round($value, 2))->toBe(1600.0);

    // Issue 120 units @ avg cost
    $this->service->recordStockOut($stock, 120);

    $stock->refresh();
    expect($stock->quantity)->toBe(30);

    // Remaining value = 30 * 10.6667 = 320
    $value = $this->service->calculateStockValue($stock);
    expect(round($value, 2))->toBe(360.0);
});

it('calculates standard cost valuation correctly', function () {
    $warehouse = Warehouse::factory()->create(['valuation_method' => InventoryValuationMethod::STANDARD_COST]);
    $product = Product::factory()->create(['standard_cost' => 15.00]);
    $stock = Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 100,
    ]);

    // Standard cost = 100 * 15 = 1500
    $value = $this->service->calculateStockValue($stock);
    expect($value)->toBe(1500.0);
});

it('calculates warehouse total value', function () {
    $warehouse = Warehouse::factory()->create(['valuation_method' => InventoryValuationMethod::FIFO]);
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();

    $stock1 = Stock::factory()->create([
        'product_id' => $product1->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 0,
    ]);

    $stock2 = Stock::factory()->create([
        'product_id' => $product2->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 0,
    ]);

    $this->service->recordStockIn($stock1, 100, 10.00);
    $this->service->recordStockIn($stock2, 50, 20.00);

    $totalValue = $this->service->getWarehouseTotalValue($warehouse);

    // Total = (100 * 10) + (50 * 20) = 2000
    expect($totalValue)->toBe(2000.0);
});

it('calculates product total value across warehouses', function () {
    $warehouse1 = Warehouse::factory()->create(['valuation_method' => InventoryValuationMethod::FIFO]);
    $warehouse2 = Warehouse::factory()->create(['valuation_method' => InventoryValuationMethod::FIFO]);
    $product = Product::factory()->create();

    $stock1 = Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse1->id,
        'quantity' => 0,
    ]);

    $stock2 = Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse2->id,
        'quantity' => 0,
    ]);

    $this->service->recordStockIn($stock1, 100, 10.00);
    $this->service->recordStockIn($stock2, 50, 12.00);

    $totalValue = $this->service->getProductTotalValue($product);

    // Total = (100 * 10) + (50 * 12) = 1600
    expect($totalValue)->toBe(1600.0);
});

describe('getCategoryTotalValue', function () {
    it('calculates category total value with single warehouse', function () {
        $warehouse = Warehouse::factory()->create(['valuation_method' => InventoryValuationMethod::FIFO]);
        $category = Category::factory()->create();
        $product1 = Product::factory()->create(['category_id' => $category->id]);
        $product2 = Product::factory()->create(['category_id' => $category->id]);

        $stock1 = Stock::factory()->create([
            'product_id' => $product1->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 0,
        ]);

        $stock2 = Stock::factory()->create([
            'product_id' => $product2->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 0,
        ]);

        $this->service->recordStockIn($stock1, 100, 10.00);
        $this->service->recordStockIn($stock2, 50, 20.00);

        $totalValue = $this->service->getCategoryTotalValue($category->id);

        // Total = (100 * 10) + (50 * 20) = 2000
        expect($totalValue)->toBe(2000.0);
    });

    it('calculates category total value across multiple warehouses', function () {
        $warehouse1 = Warehouse::factory()->create(['valuation_method' => InventoryValuationMethod::FIFO]);
        $warehouse2 = Warehouse::factory()->create(['valuation_method' => InventoryValuationMethod::FIFO]);
        $category = Category::factory()->create();
        $product1 = Product::factory()->create(['category_id' => $category->id]);
        $product2 = Product::factory()->create(['category_id' => $category->id]);

        $stock1 = Stock::factory()->create([
            'product_id' => $product1->id,
            'warehouse_id' => $warehouse1->id,
            'quantity' => 0,
        ]);

        $stock2 = Stock::factory()->create([
            'product_id' => $product2->id,
            'warehouse_id' => $warehouse2->id,
            'quantity' => 0,
        ]);

        $this->service->recordStockIn($stock1, 100, 15.00);
        $this->service->recordStockIn($stock2, 80, 25.00);

        $totalValue = $this->service->getCategoryTotalValue($category->id);

        // Total = (100 * 15) + (80 * 25) = 1500 + 2000 = 3500
        expect($totalValue)->toBe(3500.0);
    });

    it('returns zero for category with no stock', function () {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $totalValue = $this->service->getCategoryTotalValue($category->id);

        expect($totalValue)->toBe(0.0);
    });

    it('excludes products from other categories', function () {
        $warehouse = Warehouse::factory()->create(['valuation_method' => InventoryValuationMethod::FIFO]);
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        $product1 = Product::factory()->create(['category_id' => $category1->id]);
        $product2 = Product::factory()->create(['category_id' => $category2->id]);

        $stock1 = Stock::factory()->create([
            'product_id' => $product1->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 0,
        ]);

        $stock2 = Stock::factory()->create([
            'product_id' => $product2->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 0,
        ]);

        $this->service->recordStockIn($stock1, 100, 10.00);
        $this->service->recordStockIn($stock2, 50, 20.00);

        $totalValue = $this->service->getCategoryTotalValue($category1->id);

        // Should only include category1 products = 100 * 10 = 1000
        expect($totalValue)->toBe(1000.0);
    });

    it('handles different valuation methods per warehouse', function () {
        $warehouseFIFO = Warehouse::factory()->create(['valuation_method' => InventoryValuationMethod::FIFO]);
        $warehouseLIFO = Warehouse::factory()->create(['valuation_method' => InventoryValuationMethod::LIFO]);
        $category = Category::factory()->create();
        $product1 = Product::factory()->create(['category_id' => $category->id]);
        $product2 = Product::factory()->create(['category_id' => $category->id]);

        $stock1 = Stock::factory()->create([
            'product_id' => $product1->id,
            'warehouse_id' => $warehouseFIFO->id,
            'quantity' => 0,
        ]);

        $stock2 = Stock::factory()->create([
            'product_id' => $product2->id,
            'warehouse_id' => $warehouseLIFO->id,
            'quantity' => 0,
        ]);

        $this->service->recordStockIn($stock1, 50, 10.00);
        $this->service->recordStockIn($stock1, 50, 12.00);

        $this->service->recordStockIn($stock2, 50, 10.00);
        $this->service->recordStockIn($stock2, 50, 12.00);

        $totalValue = $this->service->getCategoryTotalValue($category->id);

        // Both should be (50*10 + 50*12) * 2 = 2200
        expect($totalValue)->toBe(2200.0);
    });

    it('includes only available stock quantity', function () {
        $warehouse = Warehouse::factory()->create(['valuation_method' => InventoryValuationMethod::FIFO]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $stock = Stock::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 0,
        ]);

        $this->service->recordStockIn($stock, 100, 10.00);
        $this->service->recordStockOut($stock, 60);

        $stock->refresh();

        $totalValue = $this->service->getCategoryTotalValue($category->id);

        // Remaining: 40 * 10 = 400
        expect($totalValue)->toBe(400.0);
    });
});
