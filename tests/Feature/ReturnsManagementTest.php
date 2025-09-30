<?php

declare(strict_types=1);

use App\Enums\ProductCondition;
use App\Enums\ReturnStatus;
use App\Enums\ReturnType;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ReturnDelivery;
use App\Models\ReturnLine;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Warehouse;

uses()->group('returns');

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
    $this->employee = Employee::factory()->create();
});

test('can create customer return delivery', function (): void {
    $customer = Customer::factory()->create();

    $return = ReturnDelivery::factory()->customerReturn()->create([
        'warehouse_id' => $this->warehouse->id,
        'processed_by' => $this->employee->id,
        'customer_id' => $customer->id,
    ]);

    expect($return)
        ->type->toBe(ReturnType::CUSTOMER_RETURN)
        ->customer_id->toBe($customer->id)
        ->supplier_id->toBeNull()
        ->warehouse_id->toBe($this->warehouse->id);
});

test('can create supplier return delivery', function (): void {
    $supplier = Supplier::factory()->create();

    $return = ReturnDelivery::factory()->supplierReturn()->create([
        'warehouse_id' => $this->warehouse->id,
        'processed_by' => $this->employee->id,
        'supplier_id' => $supplier->id,
    ]);

    expect($return)
        ->type->toBe(ReturnType::SUPPLIER_RETURN)
        ->supplier_id->toBe($supplier->id)
        ->customer_id->toBeNull()
        ->warehouse_id->toBe($this->warehouse->id);
});

test('can add return lines to delivery', function (): void {
    $return = ReturnDelivery::factory()->create([
        'warehouse_id' => $this->warehouse->id,
        'processed_by' => $this->employee->id,
        'total_amount' => 0,
    ]);

    $product = Product::factory()->create();

    $line = ReturnLine::factory()->create([
        'return_delivery_id' => $return->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'unit_price' => 1000,
    ]);

    expect($return->returnLines)
        ->toHaveCount(1)
        ->first()->product_id->toBe($product->id);
});

test('calculates total amount correctly', function (): void {
    $return = ReturnDelivery::factory()->create([
        'warehouse_id' => $this->warehouse->id,
        'processed_by' => $this->employee->id,
        'total_amount' => 0,
    ]);

    ReturnLine::factory()->create([
        'return_delivery_id' => $return->id,
        'quantity' => 5,
        'unit_price' => 1000,
    ]);

    ReturnLine::factory()->create([
        'return_delivery_id' => $return->id,
        'quantity' => 3,
        'unit_price' => 500,
    ]);

    $return->refresh();
    $return->refreshTotal();

    expect((float) $return->total_amount)->toBe(6500.0);
});

test('can approve return delivery', function (): void {
    $return = ReturnDelivery::factory()->draft()->create([
        'warehouse_id' => $this->warehouse->id,
        'processed_by' => $this->employee->id,
    ]);

    $return->approve();

    expect($return->status)->toBe(ReturnStatus::APPROVED);
});

test('can reject return delivery', function (): void {
    $return = ReturnDelivery::factory()->draft()->create([
        'warehouse_id' => $this->warehouse->id,
        'processed_by' => $this->employee->id,
    ]);

    $return->reject();

    expect($return->status)->toBe(ReturnStatus::REJECTED);
});

test('can restock items from approved return', function (): void {
    $product = Product::factory()->create();
    $stock = Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 10,
    ]);

    $return = ReturnDelivery::factory()->customerReturn()->approved()->create([
        'warehouse_id' => $this->warehouse->id,
        'processed_by' => $this->employee->id,
    ]);

    ReturnLine::factory()->goodCondition()->create([
        'return_delivery_id' => $return->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'condition' => ProductCondition::GOOD,
    ]);

    $return->restock();

    $stock->refresh();

    expect($stock->quantity)->toBe(15)
        ->and($return->status)->toBe(ReturnStatus::RESTOCKED);
});

test('does not restock damaged items', function (): void {
    $product = Product::factory()->create();
    $stock = Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 10,
    ]);

    $return = ReturnDelivery::factory()->customerReturn()->approved()->create([
        'warehouse_id' => $this->warehouse->id,
        'processed_by' => $this->employee->id,
    ]);

    ReturnLine::factory()->damaged()->create([
        'return_delivery_id' => $return->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'condition' => ProductCondition::DAMAGED,
    ]);

    $return->restock();

    $stock->refresh();

    expect($stock->quantity)->toBe(10)
        ->and($return->status)->toBe(ReturnStatus::RESTOCKED);
});

test('return line can determine if restockable', function (): void {
    $goodLine = ReturnLine::factory()->goodCondition()->make([
        'condition' => ProductCondition::GOOD,
    ]);

    $damagedLine = ReturnLine::factory()->damaged()->make([
        'condition' => ProductCondition::DAMAGED,
    ]);

    expect($goodLine->canBeRestocked())->toBeTrue()
        ->and($damagedLine->canBeRestocked())->toBeFalse();
});

test('return line can determine if requires disposal', function (): void {
    $goodLine = ReturnLine::factory()->goodCondition()->make([
        'condition' => ProductCondition::GOOD,
    ]);

    $damagedLine = ReturnLine::factory()->damaged()->make([
        'condition' => ProductCondition::DAMAGED,
    ]);

    expect($goodLine->requiresDisposal())->toBeFalse()
        ->and($damagedLine->requiresDisposal())->toBeTrue();
});
