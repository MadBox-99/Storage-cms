<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Batch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'batch_number',
        'manufacture_date',
        'expiry_date',
        'serial_numbers',
        'quantity',
        'supplier_id',
        'quality_status',
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'serial_numbers' => 'array',
        'quantity' => 'integer',
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stocks()
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
}
