<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use App\Models\Warehouse;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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

        Product::factory()->count(10)->create();
        $manager = Employee::factory()->create([
            'warehouse_id' => null,
        ]);
        Warehouse::factory()
            ->count(2)
            ->for($manager, 'manager')
            ->has(Stock::factory()->count(5))
            ->has(Employee::factory()->count(3))
            ->create();
        /* Employee::factory()->has($warehouses)->count(10)->create(); */

    }
}
