<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\ProductStatus;
use App\Enums\UnitType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'barcode',
        'unit_of_measure',
        'weight',
        'dimensions',
        'category_id',
        'supplier_id',
        'min_stock',
        'max_stock',
        'reorder_point',
        'price',
        'standard_cost',
        'status',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function supplierPrices(): HasMany
    {
        return $this->hasMany(SupplierPrice::class);
    }

    // Helper methods
    public function getActiveSupplierPrices()
    {
        return $this->supplierPrices()
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            })
            ->with('supplier')
            ->get();
    }

    public function getBestPrice(): ?SupplierPrice
    {
        return $this->getActiveSupplierPrices()
            ->sortBy('price')
            ->first();
    }

    // Helper methods
    public function isAvailable(): bool
    {
        return $this->stocks()->sum('quantity') > 0;
    }

    public function getTotalStock(): int
    {
        return $this->stocks()->sum('quantity');
    }

    public function calculateReorderQuantity(): int
    {
        $currentStock = $this->getTotalStock();

        return max(0, $this->max_stock - $currentStock);
    }

    public function needsReorder(): bool
    {
        return $this->getTotalStock() <= $this->reorder_point;
    }

    public function getExpectedArrivals()
    {
        return Order::query()
            ->whereHas('orderLines', fn ($query) => $query->where('product_id', $this->id))
            ->with(['orderLines' => fn ($query) => $query->where('product_id', $this->id), 'supplier'])
            ->where('type', OrderType::PURCHASE)
            ->whereIn('status', [
                OrderStatus::CONFIRMED,
                OrderStatus::PROCESSING,
                OrderStatus::SHIPPED,
            ])
            ->whereNotNull('delivery_date')
            ->orderBy('delivery_date', 'asc')
            ->get();
    }

    public function getTotalExpectedQuantity(): int
    {
        $total = 0;

        foreach ($this->getExpectedArrivals() as $order) {
            foreach ($order->orderLines as $line) {
                $total += $line->quantity;
            }
        }

        return $total;
    }

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'price' => 'decimal:2',
            'standard_cost' => 'decimal:4',
            'min_stock' => 'integer',
            'max_stock' => 'integer',
            'reorder_point' => 'integer',
            'dimensions' => 'array',
            'status' => ProductStatus::class,
            'unit_of_measure' => UnitType::class,
        ];
    }
}
