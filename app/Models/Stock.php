<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Stock extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'minimum_stock',
        'maximum_stock',
        'batch_id',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'minimum_stock' => 'integer',
        'maximum_stock' => 'integer',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
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

    // Unique constraint: one stock per product per warehouse
    protected static function boot()
    {
        parent::boot();

        self::creating(function ($stock) {
            // Ensure unique product-warehouse combination
            $existing = static::where('product_id', $stock->product_id)
                ->where('warehouse_id', $stock->warehouse_id)
                ->first();

            if ($existing) {
                throw new Exception('Stock already exists for this product in this warehouse');
            }
        });
    }
}
