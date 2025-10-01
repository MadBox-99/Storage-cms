<?php

declare(strict_types=1);

use App\Models\Invoice;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\ReceiptLine;
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
        Schema::create('invoice_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Invoice::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Product::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(OrderLine::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(ReceiptLine::class)->nullable()->constrained()->nullOnDelete();

            $table->string('description');
            $table->decimal('quantity', 15, 2);
            $table->string('unit')->nullable();
            $table->decimal('unit_price', 15, 4);
            $table->decimal('tax_rate', 5, 2)->default(27); // Default ÁFA 27%
            $table->decimal('tax_amount', 15, 2);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2);

            $table->timestamps();

            $table->index('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
    }
};
