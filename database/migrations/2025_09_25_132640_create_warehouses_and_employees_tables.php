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

        // Most hozzáadjuk a foreign key-eket mindkét táblához
        Schema::table('warehouses', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('employees')->nullOnDelete();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Először töröljük a foreign key-eket
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });

        // Aztán töröljük a táblákat
        Schema::dropIfExists('employees');
        Schema::dropIfExists('warehouses');
    }
};
