<?php

declare(strict_types=1);

use App\Models\Invoice;
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
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Invoice::class)->constrained()->cascadeOnDelete();

            $table->string('payment_number')->unique();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('HUF');
            $table->decimal('exchange_rate', 10, 6)->default(1);

            $table->string('payment_method'); // PaymentMethod enum
            $table->string('status')->default('PENDING'); // PaymentStatus enum

            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['invoice_id', 'payment_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
