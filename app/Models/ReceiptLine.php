<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ReceiptLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_id',
        'product_id',
        'warehouse_id',
        'quantity_expected',
        'quantity_received',
        'unit_price',
        'condition',
        'expiry_date',
        'batch_number',
        'note',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(Receipt::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function calculateVariance(): int
    {
        return $this->quantity_received - $this->quantity_expected;
    }

    public function calculateLineTotal(): float
    {
        return $this->quantity_received * $this->unit_price;
    }

    public function isDiscrepant(): bool
    {
        return $this->quantity_received !== $this->quantity_expected;
    }

    public function hasDefects(): bool
    {
        return $this->condition !== 'GOOD';
    }

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'expiry_date' => 'date',
        ];
    }
}
