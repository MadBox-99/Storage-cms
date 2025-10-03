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
        Schema::create('cn_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->comment('8 jegyű CN kód');
            $table->text('description')->comment('Magyar nyelvű megnevezés');
            $table->string('supplementary_unit', 50)->nullable()->comment('Kiegészítő mértékegység (pl. liter, darab)');
            $table->timestamps();

            $table->unique('code', 'idx_cn_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cn_codes');
    }
};
