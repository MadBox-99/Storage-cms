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
        Schema::table('intrastat_lines', function (Blueprint $table) {
            $table->foreignId('cn_code_id')->nullable()->after('supplier_id')->constrained('cn_codes')->nullOnDelete();
            $table->string('cn_code', 8)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intrastat_lines', function (Blueprint $table) {
            $table->dropForeign(['cn_code_id']);
            $table->dropColumn('cn_code_id');
            $table->string('cn_code', 8)->nullable(false)->change();
        });
    }
};
