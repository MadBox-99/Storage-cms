<?php

declare(strict_types=1);

use App\Models\Stock;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Notifications\OverstockAlert;
use Illuminate\Support\Facades\Notification;

it('sends low stock alert when stock quantity falls below minimum', function () {
    Notification::fake();

    $admin = User::factory()->create(['is_super_admin' => true]);

    $stock = Stock::factory()->create([
        'quantity' => 5,
        'minimum_stock' => 10,
        'maximum_stock' => 100,
    ]);

    Notification::assertSentTo($admin, LowStockAlert::class);
});

it('sends overstock alert when stock quantity exceeds maximum', function () {
    Notification::fake();

    $admin = User::factory()->create(['is_super_admin' => true]);

    $stock = Stock::factory()->create([
        'quantity' => 150,
        'minimum_stock' => 10,
        'maximum_stock' => 100,
    ]);

    Notification::assertSentTo($admin, OverstockAlert::class);
});

it('sends low stock alert when quantity is updated below minimum', function () {
    Notification::fake();

    $admin = User::factory()->create(['is_super_admin' => true]);

    $stock = Stock::factory()->create([
        'quantity' => 50,
        'minimum_stock' => 10,
        'maximum_stock' => 100,
    ]);

    // Clear any notifications from creation
    Notification::fake();

    $stock->update(['quantity' => 5]);

    Notification::assertSentTo($admin, LowStockAlert::class);
});

it('does not send low stock alert when quantity is zero', function () {
    Notification::fake();

    User::factory()->create(['is_super_admin' => true]);

    $stock = Stock::factory()->create([
        'quantity' => 0,
        'minimum_stock' => 10,
        'maximum_stock' => 100,
    ]);

    Notification::assertNotSentTo(
        User::where('is_super_admin', true)->get(),
        LowStockAlert::class
    );
});

it('sends notifications to all super admins', function () {
    Notification::fake();

    $admin1 = User::factory()->create(['is_super_admin' => true]);
    $admin2 = User::factory()->create(['is_super_admin' => true]);
    $regularUser = User::factory()->create(['is_super_admin' => false]);

    $stock = Stock::factory()->create([
        'quantity' => 5,
        'minimum_stock' => 10,
        'maximum_stock' => 100,
    ]);

    Notification::assertSentTo($admin1, LowStockAlert::class);
    Notification::assertSentTo($admin2, LowStockAlert::class);
    Notification::assertNotSentTo($regularUser, LowStockAlert::class);
});

it('does not send notification when quantity changes but stays within normal range', function () {
    Notification::fake();

    User::factory()->create(['is_super_admin' => true]);

    $stock = Stock::factory()->create([
        'quantity' => 50,
        'minimum_stock' => 10,
        'maximum_stock' => 100,
    ]);

    Notification::fake();

    $stock->update(['quantity' => 60]);

    Notification::assertNothingSent();
});

it('does not send notification when non-quantity field is updated', function () {
    Notification::fake();

    User::factory()->create(['is_super_admin' => true]);

    $stock = Stock::factory()->create([
        'quantity' => 5,
        'minimum_stock' => 10,
        'maximum_stock' => 100,
    ]);

    Notification::fake();

    $stock->update(['reserved_quantity' => 2]);

    Notification::assertNothingSent();
});

it('sends overstock alert when quantity is updated above maximum', function () {
    Notification::fake();

    $admin = User::factory()->create(['is_super_admin' => true]);

    $stock = Stock::factory()->create([
        'quantity' => 50,
        'minimum_stock' => 10,
        'maximum_stock' => 100,
    ]);

    Notification::fake();

    $stock->update(['quantity' => 150]);

    Notification::assertSentTo($admin, OverstockAlert::class);
});

it('sends both low stock and overstock alerts correctly based on thresholds', function () {
    Notification::fake();

    $admin = User::factory()->create(['is_super_admin' => true]);

    // Create stock at low level
    $lowStock = Stock::factory()->create([
        'quantity' => 5,
        'minimum_stock' => 10,
        'maximum_stock' => 100,
    ]);

    Notification::assertSentTo($admin, LowStockAlert::class);
    Notification::assertNotSentTo($admin, OverstockAlert::class);

    Notification::fake();

    // Create stock at high level
    $highStock = Stock::factory()->create([
        'quantity' => 150,
        'minimum_stock' => 10,
        'maximum_stock' => 100,
    ]);

    Notification::assertNotSentTo($admin, LowStockAlert::class);
    Notification::assertSentTo($admin, OverstockAlert::class);
});
