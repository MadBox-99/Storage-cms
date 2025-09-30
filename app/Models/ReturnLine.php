<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductCondition;
use App\Enums\ReturnReason;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function returnDelivery(): BelongsTo
    {
        return $this->belongsTo(ReturnDelivery::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function calculateLineTotal(): float
    {
        return $this->quantity * $this->unit_price;
    }

    public function canBeRestocked(): bool
    {
        return $this->condition->canBeRestocked();
    }

    public function requiresDisposal(): bool
    {
        return $this->condition->requiresDisposal();
    }

    protected function casts(): array
    {
        return [
            'condition' => ProductCondition::class,
            'return_reason' => ReturnReason::class,
            'unit_price' => 'decimal:2',
        ];
    }
}
