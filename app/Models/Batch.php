<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Batch extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'batch_number',
        'product_id',
        'supplier_id',
        'manufacture_date',
        'expiry_date',
        'serial_numbers',
        'quantity',
        'quality_status',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    // Helper methods
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getDaysUntilExpiry(): int
    {
        if (! $this->expiry_date) {
            return PHP_INT_MAX;
        }

        return max(0, Carbon::now()->diffInDays($this->expiry_date, false));
    }

    public function getTrackingNumber(): string
    {
        return $this->batch_number;
    }

    public function getHistory(): array
    {
        // TODO: Implement tracking history
        return [];
    }

    public function addHistoryEntry(array $entry): void
    {
        // TODO: Implement history tracking
    }

    protected function casts(): array
    {
        return [
            'manufacture_date' => 'date',
            'expiry_date' => 'date',
            'serial_numbers' => 'array',
            'quantity' => 'integer',
        ];
    }
}
