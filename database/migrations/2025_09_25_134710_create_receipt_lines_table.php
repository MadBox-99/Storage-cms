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
        Schema::create('receipt_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->integer('quantity_expected');
            $table->integer('quantity_received');
            $table->decimal('unit_price', 10, 2);
            $table->string('condition', 50)->default('GOOD');
            $table->string('expiry_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['receipt_id']);
            $table->index(['product_id']);
            $table->index(['warehouse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipt_lines');
    }
};
