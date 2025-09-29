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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('inventory_number', 100)->unique();
            $table->foreignId('warehouse_id')->constrained();
            $table->foreignId('conducted_by')->constrained('employees');
            $table->date('inventory_date');
            $table->string('status', 50)->default('IN_PROGRESS');
            $table->string('type', 50);
            $table->decimal('variance_value', 10, 2)->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status']);
            $table->index(['inventory_date']);
            $table->index(['warehouse_id']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
