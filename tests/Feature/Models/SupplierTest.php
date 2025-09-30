<?php

declare(strict_types=1);

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a supplier', function () {
    $supplier = Supplier::factory()->create([
        'company_name' => 'Test Supplier Inc.',
        'email' => 'test@supplier.com',
        'is_active' => true,
    ]);

    expect($supplier->company_name)->toBe('Test Supplier Inc.')
        ->and($supplier->email)->toBe('test@supplier.com')
        ->and($supplier->is_active)->toBeTrue();
});

it('can update supplier rating', function () {
    $supplier = Supplier::factory()->create(['rating' => 'GOOD']);

    $supplier->updateRating('EXCELLENT');

    expect($supplier->fresh()->rating)->toBe('EXCELLENT');
});

it('can create supplier with excellent rating using factory state', function () {
    $supplier = Supplier::factory()->excellent()->create();

    expect($supplier->rating)->toBe('EXCELLENT');
});

it('can create blacklisted supplier using factory state', function () {
    $supplier = Supplier::factory()->blacklisted()->create();

    expect($supplier->rating)->toBe('BLACKLISTED')
        ->and($supplier->is_active)->toBeFalse();
});

it('has products relationship', function () {
    $supplier = Supplier::factory()->create();

    expect($supplier->products())->toBeInstanceOf(HasMany::class);
});

it('has orders relationship', function () {
    $supplier = Supplier::factory()->create();

    expect($supplier->orders())->toBeInstanceOf(HasMany::class);
});

it('casts headquarters and mailing_address to array', function () {
    $supplier = Supplier::factory()->create();

    expect($supplier->headquarters)->toBeArray()
        ->and($supplier->mailing_address)->toBeArray();
});

it('casts is_active to boolean', function () {
    $supplier = Supplier::factory()->create(['is_active' => 1]);

    expect($supplier->is_active)->toBeBool();
});

it('can soft delete a supplier', function () {
    $supplier = Supplier::factory()->create();
    $supplierId = $supplier->id;

    $supplier->delete();

    expect(Supplier::find($supplierId))->toBeNull()
        ->and(Supplier::withTrashed()->find($supplierId))->not->toBeNull();
});

it('has unique code', function () {
    $code = 'SUP-1234';
    Supplier::factory()->create(['code' => $code]);

    expect(fn () => Supplier::factory()->create(['code' => $code]))
        ->toThrow(QueryException::class);
});
