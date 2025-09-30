<?php

declare(strict_types=1);

use App\Models\Batch;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class)->constrained();
            $table->foreignIdFor(Warehouse::class)->constrained();
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->integer('maximum_stock')->default(0);
            $table->foreignIdFor(Batch::class)->nullable()->constrained();
            $table->string('status', 50)->default('AVAILABLE');
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Unique constraint: one stock per product per warehouse
            $table->unique(['product_id', 'warehouse_id']);
            $table->index(['status']);
            $table->index(['quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
