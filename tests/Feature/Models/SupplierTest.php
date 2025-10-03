<?php

declare(strict_types=1);

use App\Enums\CountryCode;
use App\Enums\SupplierRating;
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
    $supplier = Supplier::factory()->create(['rating' => SupplierRating::GOOD]);

    $supplier->update(['rating' => SupplierRating::EXCELLENT]);

    expect($supplier->fresh()->rating)->toBe(SupplierRating::EXCELLENT);
});

it('can create supplier with excellent rating using factory state', function () {
    $supplier = Supplier::factory()->excellent()->create();

    expect($supplier->rating)->toBe(SupplierRating::EXCELLENT);
});

it('can create blacklisted supplier using factory state', function () {
    $supplier = Supplier::factory()->blacklisted()->create();

    expect($supplier->rating)->toBe(SupplierRating::BLACKLISTED)
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

it('casts country_code to CountryCode enum', function () {
    $supplier = Supplier::factory()->create([
        'country_code' => CountryCode::DE,
    ]);

    expect($supplier->country_code)->toBeInstanceOf(CountryCode::class)
        ->and($supplier->country_code)->toBe(CountryCode::DE);
});

it('casts is_eu_member to boolean', function () {
    $supplier = Supplier::factory()->create([
        'country_code' => CountryCode::DE,
        'is_eu_member' => true,
    ]);

    expect($supplier->is_eu_member)->toBeBool()
        ->and($supplier->is_eu_member)->toBeTrue();
});

it('sets is_eu_member correctly based on country_code', function () {
    $euSupplier = Supplier::factory()->create([
        'country_code' => CountryCode::DE,
    ]);

    $nonEuSupplier = Supplier::factory()->create([
        'country_code' => CountryCode::XI,
        'is_eu_member' => false,
    ]);

    expect($euSupplier->country_code->isEuMember())->toBeTrue()
        ->and($nonEuSupplier->country_code->isEuMember())->toBeFalse();
});

it('creates supplier with random country from factory', function () {
    $supplier = Supplier::factory()->create();

    expect($supplier->country_code)->toBeInstanceOf(CountryCode::class)
        ->and($supplier->is_eu_member)->toBeBool()
        ->and($supplier->is_eu_member)->toBe($supplier->country_code->isEuMember());
});

it('has country label from enum', function () {
    $supplier = Supplier::factory()->create([
        'country_code' => CountryCode::FR,
    ]);

    expect($supplier->country_code->getLabel())->toBe('Franciaország');
});

it('can create supplier with all EU member countries', function () {
    $euCountries = [
        CountryCode::AT, CountryCode::BE, CountryCode::BG, CountryCode::HR,
        CountryCode::CY, CountryCode::CZ, CountryCode::DK, CountryCode::EE,
        CountryCode::FI, CountryCode::FR, CountryCode::DE, CountryCode::GR,
        CountryCode::HU, CountryCode::IE, CountryCode::IT, CountryCode::LV,
        CountryCode::LT, CountryCode::LU, CountryCode::MT, CountryCode::NL,
        CountryCode::PL, CountryCode::PT, CountryCode::RO, CountryCode::SK,
        CountryCode::SI, CountryCode::ES, CountryCode::SE,
    ];

    foreach ($euCountries as $countryCode) {
        $supplier = Supplier::factory()->create([
            'code' => 'SUP-'.fake()->unique()->numberBetween(10000, 99999),
            'country_code' => $countryCode,
            'is_eu_member' => true,
        ]);

        expect($supplier->country_code)->toBe($countryCode)
            ->and($supplier->country_code->isEuMember())->toBeTrue();
    }
});

it('can create supplier with XI (Northern Ireland)', function () {
    $supplier = Supplier::factory()->create([
        'country_code' => CountryCode::XI,
        'is_eu_member' => false,
    ]);

    expect($supplier->country_code)->toBe(CountryCode::XI)
        ->and($supplier->country_code->isEuMember())->toBeFalse()
        ->and($supplier->country_code->getLabel())->toBe('Egyesült Királyság (Észak-Írország)');
});
