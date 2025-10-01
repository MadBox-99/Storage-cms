<?php

declare(strict_types=1);

use App\Models\IntrastatDeclaration;
use App\Models\Order;
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
        Schema::create('intrastat_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(IntrastatDeclaration::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Order::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Product::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Supplier::class)->nullable()->constrained()->nullOnDelete();

            // KN kód (Kombinált Nómenklatúra) - 8 jegyű termékkód
            $table->string('cn_code', 8);

            // Mennyiségi adatok
            $table->decimal('quantity', 15, 2);
            $table->decimal('net_mass', 15, 3); // kg-ban
            $table->string('supplementary_unit')->nullable(); // Kiegészítő mértékegység
            $table->decimal('supplementary_quantity', 15, 2)->nullable();

            // Értékadatok (HUF-ban)
            $table->decimal('invoice_value', 15, 2);
            $table->decimal('statistical_value', 15, 2);

            // Ország kódok (ISO 3166-1 alpha-2)
            $table->string('country_of_origin', 2)->nullable(); // Származási ország
            $table->string('country_of_consignment', 2); // Feladás országa (érkezésnél)
            $table->string('country_of_destination', 2); // Rendeltetési ország (feladásnál)

            // Ügylet jellege
            $table->string('transaction_type', 2); // IntrastatTransactionType enum

            // Szállítási mód
            $table->string('transport_mode', 1); // IntrastatTransportMode enum

            // Szállítási feltétel
            $table->string('delivery_terms', 3)->nullable(); // IntrastatDeliveryTerms enum

            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['intrastat_declaration_id', 'cn_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intrastat_lines');
    }
};
