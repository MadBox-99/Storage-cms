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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_number', 100)->unique();
            $table->string('type', 50); // INBOUND, OUTBOUND, TRANSFER, ADJUSTMENT, etc.
            $table->foreignId('source_warehouse_id')->nullable()->constrained('warehouses');
            $table->foreignId('target_warehouse_id')->nullable()->constrained('warehouses');
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->foreignId('batch_id')->nullable()->constrained();
            $table->string('status', 50)->default('PLANNED');
            $table->foreignId('executed_by')->nullable()->constrained('employees');
            $table->timestamp('executed_at')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type']);
            $table->index(['status']);
            $table->index(['executed_at']);
            $table->index(['product_id', 'executed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
