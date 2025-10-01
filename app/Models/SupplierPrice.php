<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SupplierPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'supplier_id',
        'price',
        'currency',
        'minimum_order_quantity',
        'lead_time_days',
        'valid_from',
        'valid_until',
        'is_active',
        'notes',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isValidAt(?string $date = null): bool
    {
        $checkDate = $date ? now()->parse($date) : now();

        if ($this->valid_from && $checkDate->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $checkDate->gt($this->valid_until)) {
            return false;
        }

        return $this->is_active;
    }

    public function isCurrentlyValid(): bool
    {
        return $this->isValidAt();
    }

    public function calculateTotalPrice(int $quantity): float
    {
        return $this->price * $quantity;
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:4',
            'minimum_order_quantity' => 'integer',
            'lead_time_days' => 'integer',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'is_active' => 'boolean',
        ];
    }
}
