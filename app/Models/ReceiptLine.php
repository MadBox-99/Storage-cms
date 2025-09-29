<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected $casts = [
        'unit_price' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
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
}
