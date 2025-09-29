<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ReturnDelivery extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'return_number',
        'type',
        'order_id',
        'customer_id',
        'supplier_id',
        'warehouse_id',
        'processed_by',
        'return_date',
        'status',
        'reason',
        'total_amount',
        'notes',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'processed_by');
    }

    public function returnLines(): HasMany
    {
        return $this->hasMany(ReturnLine::class);
    }

    public function addLine(ReturnLine $line): void
    {
        $this->returnLines()->save($line);
        $this->refreshTotal();
    }

    public function removeLine(ReturnLine $line): void
    {
        $line->delete();
        $this->refreshTotal();
    }

    public function calculateTotal(): float
    {
        return $this->returnLines->sum(function ($line): int|float {
            return $line->quantity * $line->unit_price;
        });
    }

    public function refreshTotal(): void
    {
        $this->update(['total_amount' => $this->calculateTotal()]);
    }

    public function process(): void
    {
        $this->update(['status' => 'PROCESSED']);
    }

    public function approve(): void
    {
        $this->update(['status' => 'APPROVED']);
    }

    public function reject(): void
    {
        $this->update(['status' => 'REJECTED']);
    }

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }
}
