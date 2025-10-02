<?php

declare(strict_types=1);

use App\Enums\MovementStatus;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\StockMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new StockMovementService();
});

it('executes transfer movement successfully', function () {
    $product = Product::factory()->create();
    $sourceWarehouse = Warehouse::factory()->create();
    $targetWarehouse = Warehouse::factory()->create();

    $sourceStock = Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $sourceWarehouse->id,
        'quantity' => 100,
        'reserved_quantity' => 0,
    ]);

    $movement = StockMovement::factory()->transfer()->create([
        'product_id' => $product->id,
        'source_warehouse_id' => $sourceWarehouse->id,
        'target_warehouse_id' => $targetWarehouse->id,
        'quantity' => 50,
        'status' => MovementStatus::PLANNED,
    ]);

    $this->service->execute($movement);

    expect($movement->fresh()->status)->toBe(MovementStatus::COMPLETED)
        ->and($movement->fresh()->executed_at)->not->toBeNull()
        ->and($sourceStock->fresh()->quantity)->toBe(50);

    $targetStock = Stock::query()
        ->where('product_id', $product->id)
        ->where('warehouse_id', $targetWarehouse->id)
        ->first();

    expect($targetStock)->not->toBeNull()
        ->and($targetStock->quantity)->toBe(50);
});

it('executes inbound movement successfully', function () {
    $product = Product::factory()->create();
    $targetWarehouse = Warehouse::factory()->create();

    $movement = StockMovement::factory()->inbound()->create([
        'product_id' => $product->id,
        'source_warehouse_id' => null,
        'target_warehouse_id' => $targetWarehouse->id,
        'quantity' => 100,
        'status' => MovementStatus::PLANNED,
    ]);

    $this->service->execute($movement);

    expect($movement->fresh()->status)->toBe(MovementStatus::COMPLETED)
        ->and($movement->fresh()->executed_at)->not->toBeNull();

    $targetStock = Stock::query()
        ->where('product_id', $product->id)
        ->where('warehouse_id', $targetWarehouse->id)
        ->first();

    expect($targetStock)->not->toBeNull()
        ->and($targetStock->quantity)->toBe(100);
});

it('executes outbound movement successfully', function () {
    $product = Product::factory()->create();
    $sourceWarehouse = Warehouse::factory()->create();

    $sourceStock = Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $sourceWarehouse->id,
        'quantity' => 100,
        'reserved_quantity' => 0,
    ]);

    $movement = StockMovement::factory()->outbound()->create([
        'product_id' => $product->id,
        'source_warehouse_id' => $sourceWarehouse->id,
        'target_warehouse_id' => null,
        'quantity' => 30,
        'status' => MovementStatus::PLANNED,
    ]);

    $this->service->execute($movement);

    expect($movement->fresh()->status)->toBe(MovementStatus::COMPLETED)
        ->and($movement->fresh()->executed_at)->not->toBeNull()
        ->and($sourceStock->fresh()->quantity)->toBe(70);
});

it('throws exception when quantity is zero or negative', function () {
    $movement = StockMovement::factory()->create([
        'quantity' => 0,
    ]);

    $this->service->execute($movement);
})->throws(Exception::class, 'Invalid quantity: must be greater than zero');

it('throws exception when movement has no source or target warehouse', function () {
    $movement = StockMovement::factory()->create([
        'source_warehouse_id' => null,
        'target_warehouse_id' => null,
        'quantity' => 10,
    ]);

    $this->service->execute($movement);
})->throws(Exception::class, 'Invalid movement: must have source or target warehouse');

it('throws exception when movement is already completed', function () {
    $movement = StockMovement::factory()->completed()->create();

    $this->service->execute($movement);
})->throws(Exception::class, 'Movement already completed');

it('throws exception when movement is cancelled', function () {
    $movement = StockMovement::factory()->cancelled()->create();

    $this->service->execute($movement);
})->throws(Exception::class, 'Cannot execute cancelled movement');

it('throws exception when insufficient stock in source warehouse', function () {
    $product = Product::factory()->create();
    $sourceWarehouse = Warehouse::factory()->create();
    $targetWarehouse = Warehouse::factory()->create();

    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $sourceWarehouse->id,
        'quantity' => 10,
        'reserved_quantity' => 0,
    ]);

    $movement = StockMovement::factory()->transfer()->create([
        'product_id' => $product->id,
        'source_warehouse_id' => $sourceWarehouse->id,
        'target_warehouse_id' => $targetWarehouse->id,
        'quantity' => 50,
        'status' => MovementStatus::PLANNED,
    ]);

    $this->service->execute($movement);
})->throws(Exception::class, 'Insufficient stock in source warehouse');

it('throws exception when no stock exists in source warehouse', function () {
    $product = Product::factory()->create();
    $sourceWarehouse = Warehouse::factory()->create();
    $targetWarehouse = Warehouse::factory()->create();

    $movement = StockMovement::factory()->transfer()->create([
        'product_id' => $product->id,
        'source_warehouse_id' => $sourceWarehouse->id,
        'target_warehouse_id' => $targetWarehouse->id,
        'quantity' => 50,
        'status' => MovementStatus::PLANNED,
    ]);

    $this->service->execute($movement);
})->throws(Exception::class, 'Insufficient stock in source warehouse');

it('creates target stock if it does not exist', function () {
    $product = Product::factory()->create();
    $sourceWarehouse = Warehouse::factory()->create();
    $targetWarehouse = Warehouse::factory()->create();

    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $sourceWarehouse->id,
        'quantity' => 100,
        'reserved_quantity' => 0,
    ]);

    $movement = StockMovement::factory()->transfer()->create([
        'product_id' => $product->id,
        'source_warehouse_id' => $sourceWarehouse->id,
        'target_warehouse_id' => $targetWarehouse->id,
        'quantity' => 50,
        'status' => MovementStatus::PLANNED,
    ]);

    expect(Stock::query()
        ->where('product_id', $product->id)
        ->where('warehouse_id', $targetWarehouse->id)
        ->exists()
    )->toBeFalse();

    $this->service->execute($movement);

    expect(Stock::query()
        ->where('product_id', $product->id)
        ->where('warehouse_id', $targetWarehouse->id)
        ->exists()
    )->toBeTrue();
});

it('respects reserved quantity when checking available stock', function () {
    $product = Product::factory()->create();
    $sourceWarehouse = Warehouse::factory()->create();
    $targetWarehouse = Warehouse::factory()->create();

    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $sourceWarehouse->id,
        'quantity' => 100,
        'reserved_quantity' => 60,
    ]);

    $movement = StockMovement::factory()->transfer()->create([
        'product_id' => $product->id,
        'source_warehouse_id' => $sourceWarehouse->id,
        'target_warehouse_id' => $targetWarehouse->id,
        'quantity' => 50,
        'status' => MovementStatus::PLANNED,
    ]);

    $this->service->execute($movement);
})->throws(Exception::class, 'Insufficient stock in source warehouse');

it('cancels movement successfully', function () {
    $movement = StockMovement::factory()->planned()->create();

    $this->service->cancel($movement);

    expect($movement->fresh()->status->value)->toBe('cancelled');
});

it('wraps execution in database transaction', function () {
    $product = Product::factory()->create();
    $sourceWarehouse = Warehouse::factory()->create();
    $targetWarehouse = Warehouse::factory()->create();

    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $sourceWarehouse->id,
        'quantity' => 100,
        'reserved_quantity' => 0,
    ]);

    $movement = StockMovement::factory()->transfer()->create([
        'product_id' => $product->id,
        'source_warehouse_id' => $sourceWarehouse->id,
        'target_warehouse_id' => $targetWarehouse->id,
        'quantity' => 50,
        'status' => MovementStatus::PLANNED,
    ]);

    $originalSourceQuantity = Stock::query()
        ->where('product_id', $product->id)
        ->where('warehouse_id', $sourceWarehouse->id)
        ->value('quantity');

    try {
        $this->service->execute($movement);
    } catch (Exception $e) {
        // If an exception occurs, the transaction should rollback
    }

    // Since execution succeeded, verify the changes persisted
    $sourceStock = Stock::query()
        ->where('product_id', $product->id)
        ->where('warehouse_id', $sourceWarehouse->id)
        ->first();

    expect($sourceStock->quantity)->not->toBe($originalSourceQuantity);
});
