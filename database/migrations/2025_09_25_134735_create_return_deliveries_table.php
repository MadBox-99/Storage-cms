<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\Warehouse;
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
        Schema::create('return_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('return_number', 100)->unique();
            $table->string('type', 50);
            $table->foreignIdFor(Order::class)->nullable()->constrained();
            $table->foreignIdFor(Customer::class)->nullable()->constrained();
            $table->foreignIdFor(Supplier::class)->nullable()->constrained();
            $table->foreignIdFor(Warehouse::class)->constrained();
            $table->foreignIdFor(Employee::class, 'processed_by')->constrained();
            $table->date('return_date');
            $table->string('status', 50)->default('DRAFT');
            $table->string('reason', 100);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type']);
            $table->index(['status']);
            $table->index(['return_date']);
            $table->index(['warehouse_id']);
            $table->index(['order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_deliveries');
    }
};
