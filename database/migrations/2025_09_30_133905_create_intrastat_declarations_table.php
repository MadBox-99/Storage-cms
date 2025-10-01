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
        Schema::create('intrastat_declarations', function (Blueprint $table): void {
            $table->id();
            $table->string('declaration_number')->unique();
            $table->string('direction'); // ARRIVAL or DISPATCH
            $table->integer('reference_year');
            $table->integer('reference_month');
            $table->date('declaration_date');
            $table->date('submitted_at')->nullable();
            $table->string('submitted_by')->nullable();
            $table->decimal('total_invoice_value', 15, 2)->default(0);
            $table->decimal('total_statistical_value', 15, 2)->default(0);
            $table->decimal('total_net_mass', 15, 3)->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('DRAFT'); // DRAFT, SUBMITTED, ACCEPTED, REJECTED
            $table->timestamps();

            $table->index(['reference_year', 'reference_month', 'direction'], 'idx_intrastat_year_month_dir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intrastat_declarations');
    }
};
