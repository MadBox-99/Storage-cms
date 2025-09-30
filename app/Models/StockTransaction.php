<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_id',
        'product_id',
        'warehouse_id',
        'type',
        'quantity',
        'unit_cost',
        'total_cost',
        'remaining_quantity',
        'reference_type',
        'reference_id',
        'notes',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function isFullyConsumed(): bool
    {
        return $this->remaining_quantity === 0;
    }

    public function hasRemainingQuantity(): bool
    {
        return $this->remaining_quantity > 0;
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:2',
            'remaining_quantity' => 'integer',
        ];
    }
}
