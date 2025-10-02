<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create an order', function (): void {
    $order = Order::factory()->create([
        'order_number' => 'ORD-001',
        'total_amount' => 100.50,
        'status' => OrderStatus::DRAFT,
    ]);

    expect($order->order_number)->toBe('ORD-001');
    expect($order->total_amount)->toBe('100.50'); // Database returns string for decimal
    expect($order->status)->toBe(OrderStatus::DRAFT);
});

it('casts status to enum', function (): void {
    $order = Order::factory()->create([
        'status' => OrderStatus::DRAFT,
    ]);

    expect($order->status)->toBeInstanceOf(OrderStatus::class);
    expect($order->status)->toBe(OrderStatus::DRAFT);
});

it('casts dates correctly', function (): void {
    $order = Order::factory()->create([
        'order_date' => '2023-12-01',
        'delivery_date' => '2023-12-15',
    ]);

    expect($order->order_date)->toBeInstanceOf(Carbon::class);
    expect($order->delivery_date)->toBeInstanceOf(Carbon::class);
});

it('belongs to customer', function (): void {
    $customer = Customer::factory()->create();
    $order = Order::factory()->create(['customer_id' => $customer->id]);

    expect($order->customer)->toBeInstanceOf(Customer::class);
    expect($order->customer->id)->toBe($customer->id);
});

it('belongs to supplier', function (): void {
    $supplier = Supplier::factory()->create();
    $order = Order::factory()->create(['supplier_id' => $supplier->id]);

    expect($order->supplier)->toBeInstanceOf(Supplier::class);
    expect($order->supplier->id)->toBe($supplier->id);
});

it('has many order lines', function (): void {
    $order = Order::factory()->create();
    $product = Product::factory()->create();

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 25.00,
    ]);

    expect($order->orderLines)->toHaveCount(1);
    expect($order->orderLines->first())->toBeInstanceOf(OrderLine::class);
});

it('calculates total correctly', function (): void {
    $order = Order::factory()->create();
    $product = Product::factory()->create();

    // Create order lines
    OrderLine::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 25.00,
        'discount_percent' => 0,
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 30.00,
        'discount_percent' => 10, // 10% discount
    ]);

    $total = $order->calculateTotal();
    // (2 * 25.00) + (1 * 30.00 * 0.9) = 50.00 + 27.00 = 77.00
    expect($total)->toBe(77.0);
});

it('can process order', function (): void {
    $order = Order::factory()->create(['status' => OrderStatus::DRAFT]);

    $order->process();

    expect($order->fresh()->status)->toBe(OrderStatus::PROCESSING);
});

it('can cancel order', function (): void {
    $order = Order::factory()->create(['status' => OrderStatus::DRAFT]);

    $order->cancel();

    expect($order->fresh()->status)->toBe(OrderStatus::CANCELLED);
});

it('returns tracking number', function (): void {
    $order = Order::factory()->create(['order_number' => 'ORD-12345']);

    expect($order->getTrackingNumber())->toBe('ORD-12345');
});

it('can refresh total amount', function (): void {
    $order = Order::factory()->create(['total_amount' => 0]);
    $product = Product::factory()->create();

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'unit_price' => 15.00,
        'discount_percent' => 0,
    ]);

    $order->refreshTotal();

    expect($order->fresh()->total_amount)->toBe('45.00');
});

it('can add order line', function (): void {
    $order = Order::factory()->create();
    $product = Product::factory()->create();

    $orderLine = new OrderLine([
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 50.00,
        'discount_percent' => 0,
    ]);

    $order->addLine($orderLine);

    expect($order->orderLines)->toHaveCount(1);
    expect($order->fresh()->total_amount)->toBe('50.00');
});

it('can remove order line', function (): void {
    $order = Order::factory()->create(['total_amount' => 100.00]);
    $product = Product::factory()->create();

    $orderLine = OrderLine::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 50.00,
        'discount_percent' => 0,
    ]);

    $order->removeLine($orderLine);

    expect($order->orderLines)->toHaveCount(0);
    expect($order->fresh()->total_amount)->toBe('0.00');
});

it('casts shipping address to array', function (): void {
    $address = [
        'street' => '123 Main St',
        'city' => 'Budapest',
        'zip' => '1051',
        'country' => 'Hungary',
    ];

    $order = Order::factory()->create([
        'shipping_address' => $address,
    ]);

    expect($order->shipping_address)->toBeArray();
    expect($order->shipping_address['city'])->toBe('Budapest');
});
