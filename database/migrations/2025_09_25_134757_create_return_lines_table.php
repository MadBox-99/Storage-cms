<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ReturnDelivery;
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
        Schema::create('return_delivery_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ReturnDelivery::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Product::class)->constrained();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->string('condition', 50);
            $table->string('return_reason', 100);
            $table->string('batch_number')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['return_delivery_id']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_delivery_lines');
    }
};
