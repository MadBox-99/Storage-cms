<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Warehouse;
use BladeUI\Icons\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);

        $products = Product::factory()->count(10)->create();

        $manager = Employee::factory()->create([
            'warehouse_id' => null,
        ]);
        $warehouses = Warehouse::factory()
            ->count(2)
            ->for($manager, 'manager')
            ->has(Employee::factory()->count(3))
            ->create();
        $warehouses->each(fn (Warehouse $warehouse) => Stock::factory()
            ->count(10)
            ->for($warehouse, 'warehouse')
            ->state(new Sequence(
                fn (Sequence $sequence): array => [
                    'product_id' => $products->get($sequence->index)->id,
                ]
            ))->create());

        Customer::factory()->count(10)->create();

        $this->call([
            IntraStatSeeder::class,
        ]);

    }
}
