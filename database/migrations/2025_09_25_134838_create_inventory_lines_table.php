<?php

declare(strict_types=1);

use App\Models\Inventory;
use App\Models\Product;
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
        Schema::create('inventory_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Inventory::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Product::class)->constrained();
            $table->integer('system_quantity');
            $table->integer('actual_quantity');
            $table->decimal('unit_cost', 10, 2);
            $table->string('condition', 50)->default('GOOD');
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['inventory_id']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_lines');
    }
};
