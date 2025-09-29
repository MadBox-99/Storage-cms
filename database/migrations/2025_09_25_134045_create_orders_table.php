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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 100)->unique();
            $table->string('type', 50); // PURCHASE, SALES, TRANSFER, RETURN
            $table->foreignId('customer_id')->nullable()->constrained();
            $table->foreignId('supplier_id')->nullable()->constrained();
            $table->string('status', 50)->default('DRAFT');
            $table->date('order_date');
            $table->date('delivery_date')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->json('shipping_address')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type']);
            $table->index(['status']);
            $table->index(['order_date']);
            $table->index(['customer_id']);
            $table->index(['supplier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
