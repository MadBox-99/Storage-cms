<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class ReturnLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_delivery_id',
        'product_id',
        'quantity',
        'unit_price',
        'condition',
        'return_reason',
        'batch_number',
        'note',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function returnDelivery()
    {
        return $this->belongsTo(ReturnDelivery::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function calculateLineTotal(): float
    {
        return $this->quantity * $this->unit_price;
    }

    public function canBeRestocked(): bool
    {
        return in_array($this->condition, ['GOOD', 'MINOR_DAMAGE']);
    }

    public function requiresDisposal(): bool
    {
        return in_array($this->condition, ['DAMAGED', 'EXPIRED', 'DEFECTIVE']);
    }
}
