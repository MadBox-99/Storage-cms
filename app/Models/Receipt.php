<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Receipt extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'receipt_number',
        'order_id',
        'warehouse_id',
        'received_by',
        'receipt_date',
        'status',
        'total_amount',
        'notes',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'received_by');
    }

    public function receiptLines(): HasMany
    {
        return $this->hasMany(ReceiptLine::class);
    }

    public function addLine(ReceiptLine $line): void
    {
        $this->receiptLines()->save($line);
        $this->refreshTotal();
    }

    public function removeLine(ReceiptLine $line): void
    {
        $line->delete();
        $this->refreshTotal();
    }

    public function calculateTotal(): float
    {
        return $this->receiptLines->sum(function ($line): int|float {
            return $line->quantity_received * $line->unit_price;
        });
    }

    public function refreshTotal(): void
    {
        $this->update(['total_amount' => $this->calculateTotal()]);
    }

    public function confirm(): void
    {
        $this->update(['status' => 'CONFIRMED']);
    }

    public function reject(): void
    {
        $this->update(['status' => 'REJECTED']);
    }

    protected function casts(): array
    {
        return [
            'receipt_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }
}
