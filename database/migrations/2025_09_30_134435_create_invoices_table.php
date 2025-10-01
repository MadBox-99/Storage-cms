<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\Order;
use App\Models\Receipt;
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
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignIdFor(Order::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Receipt::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Supplier::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Customer::class)->nullable()->constrained()->nullOnDelete();

            $table->string('type'); // PURCHASE or SALE
            $table->string('status')->default('DRAFT');

            $table->date('invoice_date');
            $table->date('due_date');
            $table->date('payment_date')->nullable();

            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);

            $table->string('currency', 3)->default('HUF');
            $table->decimal('exchange_rate', 10, 6)->default(1);

            $table->text('notes')->nullable();
            $table->json('billing_address')->nullable();
            $table->json('shipping_address')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['invoice_date', 'type']);
            $table->index(['status', 'type']);
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
