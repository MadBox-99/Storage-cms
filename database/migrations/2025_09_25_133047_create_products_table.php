<?php

declare(strict_types=1);

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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 100)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('barcode', 100)->nullable();
            $table->string('unit_of_measure', 50);
            $table->decimal('weight', 8, 2)->nullable();
            $table->json('dimensions')->nullable();
            $table->foreignId('category_id')->constrained();
            $table->foreignId('supplier_id')->constrained();
            $table->integer('min_stock')->default(0);
            $table->integer('max_stock')->default(0);
            $table->integer('reorder_point')->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('status', 50)->default('ACTIVE');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status']);
            $table->index(['category_id']);
            $table->index(['supplier_id']);
            $table->index(['sku']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
