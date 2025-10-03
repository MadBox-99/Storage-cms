<?php

declare(strict_types=1);

use App\Enums\CountryCode;
use App\Enums\IntrastatDeliveryTerms;
use App\Enums\IntrastatDirection;
use App\Enums\IntrastatStatus;
use App\Enums\IntrastatTransactionType;
use App\Enums\IntrastatTransportMode;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Customer;
use App\Models\IntrastatDeclaration;
use App\Models\IntrastatLine;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\IntrastatService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new IntrastatService();
});

it('generates declaration for period with arrival direction', function () {
    $supplier = Supplier::factory()->create([
        'country_code' => CountryCode::DE,
        'is_eu_member' => true,
    ]);

    $product = Product::factory()->create([
        'cn_code' => '12345678',
        'net_weight_kg' => 1.5,
    ]);

    $order = Order::factory()->create([
        'type' => OrderType::PURCHASE,
        'status' => OrderStatus::COMPLETED,
        'supplier_id' => $supplier->id,
        'order_date' => '2025-01-15',
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_price' => 10000,
    ]);

    $declaration = $this->service->generateDeclarationForPeriod(2025, 1, IntrastatDirection::ARRIVAL);

    expect($declaration)->toBeInstanceOf(IntrastatDeclaration::class)
        ->and($declaration->direction)->toBe(IntrastatDirection::ARRIVAL)
        ->and($declaration->reference_year)->toBe(2025)
        ->and($declaration->reference_month)->toBe(1)
        ->and($declaration->status)->toBe(IntrastatStatus::DRAFT)
        ->and($declaration->intrastatLines()->count())->toBeGreaterThan(0);
});

it('generates declaration for period with dispatch direction', function () {
    $customer = Customer::factory()->create();

    $product = Product::factory()->create([
        'cn_code' => '87654321',
        'net_weight_kg' => 2.5,
    ]);

    $order = Order::factory()->create([
        'type' => OrderType::SALE,
        'status' => OrderStatus::COMPLETED,
        'customer_id' => $customer->id,
        'order_date' => '2025-02-10',
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'unit_price' => 10000,
    ]);

    $declaration = $this->service->generateDeclarationForPeriod(2025, 2, IntrastatDirection::DISPATCH);

    expect($declaration)->toBeInstanceOf(IntrastatDeclaration::class)
        ->and($declaration->direction)->toBe(IntrastatDirection::DISPATCH)
        ->and($declaration->reference_year)->toBe(2025)
        ->and($declaration->reference_month)->toBe(2)
        ->and($declaration->status)->toBe(IntrastatStatus::DRAFT);
});

it('generates declaration number correctly', function () {
    $declaration = $this->service->generateDeclarationForPeriod(2025, 3, IntrastatDirection::ARRIVAL);

    expect($declaration->declaration_number)->toContain('INTRASTAT-202503-A');
});

it('calculates totals after generating lines', function () {
    $supplier = Supplier::factory()->create([
        'country_code' => CountryCode::FR,
        'is_eu_member' => true,
    ]);

    $product = Product::factory()->create([
        'cn_code' => '11223344',
        'net_weight_kg' => 3.0,
    ]);

    $order = Order::factory()->create([
        'type' => OrderType::PURCHASE,
        'status' => OrderStatus::COMPLETED,
        'supplier_id' => $supplier->id,
        'order_date' => '2025-01-20',
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 20,
        'unit_price' => 10000,
    ]);

    $declaration = $this->service->generateDeclarationForPeriod(2025, 1, IntrastatDirection::ARRIVAL);

    expect($declaration->total_invoice_value)->toBeGreaterThan(0)
        ->and($declaration->total_statistical_value)->toBeGreaterThan(0)
        ->and($declaration->total_net_mass)->toBeGreaterThan(0);
});

it('exports declaration to XML successfully', function () {
    $declaration = IntrastatDeclaration::factory()
        ->arrival()
        ->withTotals(100000, 100000, 150.5)
        ->create();

    IntrastatLine::factory()
        ->forArrival()
        ->create([
            'intrastat_declaration_id' => $declaration->id,
            'cn_code' => '12345678',
            'quantity' => 10,
            'net_mass' => 15.5,
            'invoice_value' => 100000,
            'statistical_value' => 100000,
            'transaction_type' => IntrastatTransactionType::OUTRIGHT_PURCHASE_SALE,
            'transport_mode' => IntrastatTransportMode::ROAD,
            'delivery_terms' => IntrastatDeliveryTerms::EXW,
        ]);

    $xml = $this->service->exportToXml($declaration);

    expect($xml)->toContain('<?xml version="1.0" encoding="UTF-8"?>')
        ->and($xml)->toContain('<INTRASTAT>')
        ->and($xml)->toContain('<HEADER>')
        ->and($xml)->toContain('<ITEMS>')
        ->and($xml)->toContain('<SUMMARY>')
        ->and($xml)->toContain('<CN_CODE>12345678</CN_CODE>')
        ->and($xml)->toContain('<FLOW_CODE>A</FLOW_CODE>');
});

it('exports XML with correct flow code for dispatch', function () {
    $declaration = IntrastatDeclaration::factory()
        ->dispatch()
        ->create();

    IntrastatLine::factory()
        ->forDispatch()
        ->create([
            'intrastat_declaration_id' => $declaration->id,
        ]);

    $xml = $this->service->exportToXml($declaration);

    expect($xml)->toContain('<FLOW_CODE>D</FLOW_CODE>');
});

it('validates declaration with no lines', function () {
    $declaration = IntrastatDeclaration::factory()->create();

    $errors = $this->service->validateDeclaration($declaration);

    expect($errors)->toContain('Declaration must have at least one line');
});

it('validates line with invalid CN code', function () {
    $declaration = IntrastatDeclaration::factory()->create();

    IntrastatLine::factory()->create([
        'intrastat_declaration_id' => $declaration->id,
        'cn_code' => '123', // Invalid: not 8 digits
    ]);

    $errors = $this->service->validateDeclaration($declaration);

    expect($errors)->toHaveCount(1)
        ->and($errors[0])->toContain('KN kód kötelező, pontosan 8 számjegyből kell állnia');
});

it('validates line with invalid net mass', function () {
    $declaration = IntrastatDeclaration::factory()->create();

    IntrastatLine::factory()->create([
        'intrastat_declaration_id' => $declaration->id,
        'cn_code' => '12345678',
        'net_mass' => 0.0001, // Invalid: less than 0.001 kg
    ]);

    $errors = $this->service->validateDeclaration($declaration);

    expect($errors)->toContain('Sor 1: Nettó tömeg kötelező, minimum 0.001 kg');
});

it('validates line with invalid invoice value', function () {
    $declaration = IntrastatDeclaration::factory()->create();

    IntrastatLine::factory()->create([
        'intrastat_declaration_id' => $declaration->id,
        'cn_code' => '12345678',
        'net_mass' => 1.0,
        'invoice_value' => 0, // Invalid: less than 1 HUF
    ]);

    $errors = $this->service->validateDeclaration($declaration);

    expect($errors)->toContain('Sor 1: Számlaérték kötelező, minimum 1 HUF');
});

it('validates line with invalid country code', function () {
    $declaration = IntrastatDeclaration::factory()->create();

    IntrastatLine::factory()->create([
        'intrastat_declaration_id' => $declaration->id,
        'cn_code' => '12345678',
        'net_mass' => 1.0,
        'invoice_value' => 1000,
        'statistical_value' => 1000,
        'country_of_consignment' => 'US', // Invalid: not EU member
    ]);

    $errors = $this->service->validateDeclaration($declaration);

    expect($errors)->toContain('Sor 1: Feladás országa érvénytelen (csak EU tagállamok, HU kivételével)');
});

it('skips orders without CN code when generating lines', function () {
    $supplier = Supplier::factory()->create([
        'country_code' => CountryCode::DE,
        'is_eu_member' => true,
    ]);

    $product = Product::factory()->create([
        'cn_code' => null, // No CN code
    ]);

    $order = Order::factory()->create([
        'type' => OrderType::PURCHASE,
        'status' => OrderStatus::COMPLETED,
        'supplier_id' => $supplier->id,
        'order_date' => '2025-01-15',
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_price' => 10000,
    ]);

    $declaration = $this->service->generateDeclarationForPeriod(2025, 1, IntrastatDirection::ARRIVAL);

    expect($declaration->intrastatLines()->count())->toBe(0);
});

it('skips non-EU suppliers when generating arrival lines', function () {
    $supplier = Supplier::factory()->create([
        'country_code' => CountryCode::US,
        'is_eu_member' => false, // Non-EU supplier
    ]);

    $product = Product::factory()->create([
        'cn_code' => '12345678',
    ]);

    $order = Order::factory()->create([
        'type' => OrderType::PURCHASE,
        'status' => OrderStatus::COMPLETED,
        'supplier_id' => $supplier->id,
        'order_date' => '2025-01-15',
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_price' => 10000,
    ]);

    $declaration = $this->service->generateDeclarationForPeriod(2025, 1, IntrastatDirection::ARRIVAL);

    expect($declaration->intrastatLines()->count())->toBe(0);
});

it('skips Hungarian suppliers when generating arrival lines', function () {
    $supplier = Supplier::factory()->create([
        'country_code' => CountryCode::HU,
        'is_eu_member' => true,
    ]);

    $product = Product::factory()->create([
        'cn_code' => '12345678',
    ]);

    $order = Order::factory()->create([
        'type' => OrderType::PURCHASE,
        'status' => OrderStatus::COMPLETED,
        'supplier_id' => $supplier->id,
        'order_date' => '2025-01-15',
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_price' => 10000,
    ]);

    $declaration = $this->service->generateDeclarationForPeriod(2025, 1, IntrastatDirection::ARRIVAL);

    expect($declaration->intrastatLines()->count())->toBe(0);
});

it('includes supplementary unit in XML if provided', function () {
    $declaration = IntrastatDeclaration::factory()->create();

    IntrastatLine::factory()
        ->withSupplementaryUnit()
        ->create([
            'intrastat_declaration_id' => $declaration->id,
            'supplementary_unit' => 'p/st',
            'supplementary_quantity' => 100,
        ]);

    $xml = $this->service->exportToXml($declaration);

    expect($xml)->toContain('<SUPPLEMENTARY_UNIT>p/st</SUPPLEMENTARY_UNIT>')
        ->and($xml)->toContain('<SUPPLEMENTARY_QUANTITY>100.00</SUPPLEMENTARY_QUANTITY>');
});

it('includes country of origin for arrivals in XML', function () {
    $declaration = IntrastatDeclaration::factory()
        ->arrival()
        ->create();

    IntrastatLine::factory()
        ->forArrival()
        ->create([
            'intrastat_declaration_id' => $declaration->id,
            'country_of_origin' => 'CN',
        ]);

    $xml = $this->service->exportToXml($declaration);

    expect($xml)->toContain('<COUNTRY_OF_ORIGIN>CN</COUNTRY_OF_ORIGIN>');
});

it('does not include country of origin for dispatches in XML', function () {
    $declaration = IntrastatDeclaration::factory()
        ->dispatch()
        ->create();

    IntrastatLine::factory()
        ->forDispatch()
        ->create([
            'intrastat_declaration_id' => $declaration->id,
            'country_of_origin' => 'HU',
        ]);

    $xml = $this->service->exportToXml($declaration);

    expect($xml)->not->toContain('<COUNTRY_OF_ORIGIN>');
});

it('uses transaction within generateDeclarationForPeriod', function () {
    $supplier = Supplier::factory()->create([
        'country_code' => CountryCode::DE,
        'is_eu_member' => true,
    ]);

    $product = Product::factory()->create([
        'cn_code' => '12345678',
        'net_weight_kg' => 1.5,
    ]);

    $order = Order::factory()->create([
        'type' => OrderType::PURCHASE,
        'status' => OrderStatus::COMPLETED,
        'supplier_id' => $supplier->id,
        'order_date' => '2025-01-15',
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_price' => 10000,
    ]);

    $initialDeclarationCount = IntrastatDeclaration::count();

    $declaration = $this->service->generateDeclarationForPeriod(2025, 1, IntrastatDirection::ARRIVAL);

    expect(IntrastatDeclaration::count())->toBe($initialDeclarationCount + 1)
        ->and($declaration->exists)->toBeTrue();
});

it('exports declaration to iFORM XML for KSH-Elektra submission', function () {
    $declaration = IntrastatDeclaration::factory()
        ->dispatch()
        ->withTotals(360000, 360000, 20.0)
        ->create([
            'reference_year' => 2025,
            'reference_month' => 10,
        ]);

    IntrastatLine::factory()
        ->forDispatch()
        ->create([
            'intrastat_declaration_id' => $declaration->id,
            'cn_code' => '84821010',
            'quantity' => 10,
            'net_mass' => 20.0,
            'invoice_value' => 360000,
            'statistical_value' => 360000,
            'country_of_destination' => 'AT',
            'transaction_type' => IntrastatTransactionType::OUTRIGHT_PURCHASE_SALE,
            'transport_mode' => IntrastatTransportMode::ROAD,
            'delivery_terms' => IntrastatDeliveryTerms::FOB,
        ]);

    $xml = $this->service->exportToIFormXml($declaration);

    expect($xml)->toContain('<?xml version="1.0" encoding="UTF-8"?>')
        ->and($xml)->toContain('<form xmlns="http://iform-html.kdiv.hu/schemas/form">')
        ->and($xml)->toContain('<keys>')
        ->and($xml)->toContain('<name>iformVersion</name>')
        ->and($xml)->toContain('<templateKeys>')
        ->and($xml)->toContain('<name>OSAP</name>')
        ->and($xml)->toContain('<value>2010</value>') // OSAP 2010 for dispatch
        ->and($xml)->toContain('<chapter s="P">')
        ->and($xml)->toContain('<table name="Termek">')
        ->and($xml)->toContain('<identifier>TEKOD</identifier>')
        ->and($xml)->toContain('<value>84821010</value>')
        ->and($xml)->toContain('<identifier>SZAORSZ</identifier>')
        ->and($xml)->toContain('<value>AT</value>')
        ->and($xml)->toContain('<identifier>KGM</identifier>')
        ->and($xml)->toContain('<value>20.000</value>');
});

it('exports arrival declaration to iFORM XML with correct OSAP code', function () {
    $declaration = IntrastatDeclaration::factory()
        ->arrival()
        ->withTotals(100000, 100000, 15.5)
        ->create([
            'reference_year' => 2025,
            'reference_month' => 10,
        ]);

    IntrastatLine::factory()
        ->forArrival()
        ->create([
            'intrastat_declaration_id' => $declaration->id,
            'cn_code' => '87088099',
            'quantity' => 5,
            'net_mass' => 15.5,
            'invoice_value' => 100000,
            'statistical_value' => 100000,
            'country_of_consignment' => 'DE',
            'country_of_origin' => 'DE',
            'transaction_type' => IntrastatTransactionType::OUTRIGHT_PURCHASE_SALE,
            'transport_mode' => IntrastatTransportMode::ROAD,
            'delivery_terms' => IntrastatDeliveryTerms::EXW,
        ]);

    $xml = $this->service->exportToIFormXml($declaration);

    expect($xml)->toContain('<value>2012</value>') // OSAP 2012 for arrival
        ->and($xml)->toContain('<identifier>FTA</identifier>') // FTA for arrival instead of RTA
        ->and($xml)->toContain('<identifier>STAERT</identifier>') // STAERT for arrival instead of SZAOSSZ
        ->and($xml)->toContain('<identifier>SZSZAORSZ</identifier>'); // Country of origin for arrival
});
