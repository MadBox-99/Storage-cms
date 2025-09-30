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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('type', 50); // MAIN, DISTRIBUTION, RETAIL, etc.
            $table->integer('capacity')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable(); // FK nélkül először
            $table->boolean('is_active')->default(true);
            $table->string('valuation_method')->default('fifo');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['manager_id']);
            $table->index(['is_active']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
