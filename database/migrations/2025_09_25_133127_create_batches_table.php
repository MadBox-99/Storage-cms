<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Supplier;
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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number', 100)->unique();
            $table->foreignIdFor(Product::class)->constrained();
            $table->foreignIdFor(Supplier::class)->constrained();
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->json('serial_numbers')->nullable();
            $table->integer('quantity');
            $table->string('quality_status', 50)->default('PENDING_CHECK');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['expiry_date']);
            $table->index(['quality_status']);
            $table->index(['batch_number']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
