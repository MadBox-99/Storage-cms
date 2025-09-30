<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InventoryValuationMethod;
use App\Enums\WarehouseType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Warehouse extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'address',
        'type',
        'capacity',
        'manager_id',
        'is_active',
        'valuation_method',
        'is_consignment',
        'owner_supplier_id',
    ];

    // Relationships
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function ownerSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'owner_supplier_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    // Helper methods
    public function getAvailableCapacity(): int
    {
        // TODO: Calculate based on current stock
        return $this->capacity;
    }

    public function findProduct(Product $product): ?Stock
    {
        return $this->stocks()->where('product_id', $product->id)->first();
    }

    public function isConsignment(): bool
    {
        return $this->is_consignment;
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_consignment' => 'boolean',
            'capacity' => 'integer',
            'valuation_method' => InventoryValuationMethod::class,
            'type' => WarehouseType::class,
        ];
    }
}
