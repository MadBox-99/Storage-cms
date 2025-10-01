<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierPrice;

uses()->group('price-management');

test('product can have multiple supplier prices', function (): void {
    $product = Product::factory()->create(['name' => 'Csavar']);
    $supplierA = Supplier::factory()->create(['company_name' => 'A Szállító']);
    $supplierB = Supplier::factory()->create(['company_name' => 'B Szállító']);

    SupplierPrice::factory()->create([
        'product_id' => $product->id,
        'supplier_id' => $supplierA->id,
        'price' => 3.0000,
    ]);

    SupplierPrice::factory()->create([
        'product_id' => $product->id,
        'supplier_id' => $supplierB->id,
        'price' => 2.5000,
    ]);

    expect($product->supplierPrices)->toHaveCount(2);
});

test('can retrieve active supplier prices for product', function (): void {
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();

    SupplierPrice::factory()->active()->create([
        'product_id' => $product->id,
        'supplier_id' => $supplier->id,
        'price' => 100.0000,
    ]);

    SupplierPrice::factory()->inactive()->create([
        'product_id' => $product->id,
        'supplier_id' => $supplier->id,
        'price' => 200.0000,
        'valid_from' => now()->subDays(60),
    ]);

    $activePrices = $product->getActiveSupplierPrices();

    expect($activePrices)->toHaveCount(1)
        ->first()->price->toBe('100.0000');
});

test('can find best price for product', function (): void {
    $product = Product::factory()->create();
    $supplierA = Supplier::factory()->create();
    $supplierB = Supplier::factory()->create();
    $supplierC = Supplier::factory()->create();

    SupplierPrice::factory()->active()->create([
        'product_id' => $product->id,
        'supplier_id' => $supplierA->id,
        'price' => 150.0000,
    ]);

    SupplierPrice::factory()->active()->create([
        'product_id' => $product->id,
        'supplier_id' => $supplierB->id,
        'price' => 120.5000,
    ]);

    SupplierPrice::factory()->active()->create([
        'product_id' => $product->id,
        'supplier_id' => $supplierC->id,
        'price' => 180.0000,
    ]);

    $bestPrice = $product->getBestPrice();

    expect($bestPrice)
        ->not->toBeNull()
        ->price->toBe('120.5000')
        ->and($bestPrice->supplier_id)->toBe($supplierB->id);
});

test('expired prices are not included in active prices', function (): void {
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();

    SupplierPrice::factory()->expired()->create([
        'product_id' => $product->id,
        'supplier_id' => $supplier->id,
        'price' => 50.0000,
    ]);

    $activePrices = $product->getActiveSupplierPrices();

    expect($activePrices)->toHaveCount(0);
});

test('can check if price is currently valid', function (): void {
    $activePrice = SupplierPrice::factory()->active()->create();
    $expiredPrice = SupplierPrice::factory()->expired()->create();
    $inactivePrice = SupplierPrice::factory()->inactive()->create();

    expect($activePrice->isCurrentlyValid())->toBeTrue()
        ->and($expiredPrice->isCurrentlyValid())->toBeFalse()
        ->and($inactivePrice->isCurrentlyValid())->toBeFalse();
});

test('can calculate total price for quantity', function (): void {
    $price = SupplierPrice::factory()->create([
        'price' => 25.5000,
    ]);

    $totalPrice = $price->calculateTotalPrice(10);

    expect($totalPrice)->toBe(255.0);
});

test('product with different supplier prices shows correct values', function (): void {
    $product = Product::factory()->create(['name' => 'Csavar M8']);
    $supplierA = Supplier::factory()->create(['company_name' => 'A Szállító']);
    $supplierB = Supplier::factory()->create(['company_name' => 'B Szállító']);

    $priceA = SupplierPrice::factory()->create([
        'product_id' => $product->id,
        'supplier_id' => $supplierA->id,
        'price' => 3.0000,
    ]);

    $priceB = SupplierPrice::factory()->create([
        'product_id' => $product->id,
        'supplier_id' => $supplierB->id,
        'price' => 2.5000,
    ]);

    expect($priceA->supplier->company_name)->toBe('A Szállító')
        ->and((float) $priceA->price)->toBe(3.0)
        ->and($priceB->supplier->company_name)->toBe('B Szállító')
        ->and((float) $priceB->price)->toBe(2.5);
});
