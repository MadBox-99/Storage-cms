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
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 100)->unique();
            $table->foreignId('order_id')->nullable()->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->foreignId('received_by')->constrained('employees');
            $table->date('receipt_date');
            $table->string('status', 50)->default('PENDING');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['receipt_date']);
            $table->index(['warehouse_id']);
            $table->index(['order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
