<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Stock::class)->constrained('stocks')->cascadeOnDelete();
            $table->foreignIdFor(Product::class)->constrained('products');
            $table->foreignIdFor(Warehouse::class)->constrained('warehouses');
            $table->string('type'); // 'in' or 'out'
            $table->integer('quantity');
            $table->decimal('unit_cost', 15, 4);
            $table->decimal('total_cost', 15, 2);
            $table->integer('remaining_quantity')->default(0);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['stock_id', 'created_at']);
            $table->index(['product_id', 'warehouse_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
