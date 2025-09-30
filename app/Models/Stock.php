<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StockStatus;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Stock extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'minimum_stock',
        'maximum_stock',
        'batch_id',
        'status',
        'unit_cost',
        'total_value',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    // Helper methods
    public function getAvailableQuantity(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    public function reserve(int $quantity): bool
    {
        if ($this->getAvailableQuantity() >= $quantity) {
            $this->increment('reserved_quantity', $quantity);

            return true;
        }

        return false;
    }

    public function release(int $quantity): void
    {
        $this->decrement('reserved_quantity', min($quantity, $this->reserved_quantity));
    }

    public function updateQuantity(int $quantity): void
    {
        $this->update(['quantity' => $quantity]);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->minimum_stock;
    }

    public function isOverstock(): bool
    {
        return $this->quantity > $this->maximum_stock;
    }

    public function getStockStatus(): string
    {
        return match (true) {
            $this->quantity === 0 => 'out_of_stock',
            $this->isLowStock() => 'low_stock',
            $this->isOverstock() => 'overstock',
            default => 'normal',
        };
    }

    // Unique constraint: one stock per product per warehouse
    protected static function boot(): void
    {
        parent::boot();

        self::creating(function ($stock): void {
            // Ensure unique product-warehouse combination
            $existing = self::query()->where('product_id', $stock->product_id)
                ->where('warehouse_id', $stock->warehouse_id)
                ->first();

            if ($existing) {
                throw new Exception('Stock already exists for this product in this warehouse');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'minimum_stock' => 'integer',
            'maximum_stock' => 'integer',
            'status' => StockStatus::class,
            'unit_cost' => 'decimal:4',
            'total_value' => 'decimal:2',
        ];
    }
}
