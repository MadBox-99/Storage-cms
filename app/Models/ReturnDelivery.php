<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReturnReason;
use App\Enums\ReturnStatus;
use App\Enums\ReturnType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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
        $this->update(['status' => ReturnStatus::PROCESSED]);
    }

    public function approve(): void
    {
        $this->update(['status' => ReturnStatus::APPROVED]);
    }

    public function reject(): void
    {
        $this->update(['status' => ReturnStatus::REJECTED]);
    }

    public function restock(): void
    {
        DB::transaction(function (): void {
            foreach ($this->returnLines as $line) {
                if ($line->canBeRestocked()) {
                    $stock = Stock::query()->firstOrCreate(
                        [
                            'product_id' => $line->product_id,
                            'warehouse_id' => $this->warehouse_id,
                        ],
                        [
                            'quantity' => 0,
                            'reserved_quantity' => 0,
                            'minimum_stock' => 0,
                            'maximum_stock' => 1000,
                        ]
                    );

                    $stock->increment('quantity', $line->quantity);
                }
            }

            $this->update(['status' => ReturnStatus::RESTOCKED]);
        });
    }

    public function isCustomerReturn(): bool
    {
        return $this->type === ReturnType::CUSTOMER_RETURN;
    }

    public function isSupplierReturn(): bool
    {
        return $this->type === ReturnType::SUPPLIER_RETURN;
    }

    protected function casts(): array
    {
        return [
            'type' => ReturnType::class,
            'status' => ReturnStatus::class,
            'reason' => ReturnReason::class,
            'return_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }
}
