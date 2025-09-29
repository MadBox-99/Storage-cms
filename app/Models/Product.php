<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Product extends Model
{
    use HasFactory, SoftDeletes;

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
        'status',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'price' => 'decimal:2',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
        'reorder_point' => 'integer',
        'dimensions' => 'array',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    // Helper methods
    public function checkAvailability(): bool
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
}
