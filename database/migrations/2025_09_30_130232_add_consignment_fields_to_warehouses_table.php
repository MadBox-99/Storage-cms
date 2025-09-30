<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table): void {
            $table->boolean('is_consignment')->default(false)->after('is_active');
            $table->foreignId('owner_supplier_id')->nullable()->after('is_consignment')->constrained('suppliers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table): void {
            $table->dropForeign(['owner_supplier_id']);
            $table->dropColumn(['is_consignment', 'owner_supplier_id']);
        });
    }
};
