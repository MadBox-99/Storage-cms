<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

final class StockMovement extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'movement_number',
        'type',
        'source_warehouse_id',
        'target_warehouse_id',
        'product_id',
        'quantity',
        'batch_id',
        'status',
        'executed_by',
        'executed_at',
        'reason',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sourceWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function targetWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'target_warehouse_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function executor()
    {
        return $this->belongsTo(Employee::class, 'executed_by');
    }

    // Helper methods
    public function execute(): void
    {
        DB::transaction(function (): void {
            // Validate movement
            if (! $this->validate()) {
                throw new Exception('Invalid stock movement');
            }

            // Update source warehouse stock
            if ($this->source_warehouse_id) {
                $sourceStock = Stock::query()->where('product_id', $this->product_id)
                    ->where('warehouse_id', $this->source_warehouse_id)
                    ->first();

                if ($sourceStock && $sourceStock->getAvailableQuantity() >= $this->quantity) {
                    $sourceStock->decrement('quantity', $this->quantity);
                }
            }

            // Update target warehouse stock
            if ($this->target_warehouse_id) {
                $this->increment('quantity', $this->quantity);
                Stock::query()->updateOrCreate([
                    'product_id' => $this->product_id,
                    'warehouse_id' => $this->target_warehouse_id,
                ], []);
            }

            $this->update([
                'status' => 'COMPLETED',
                'executed_at' => now(),
            ]);
        });
    }

    public function cancel(): void
    {
        $this->update(['status' => 'CANCELLED']);
    }

    public function validate(): bool
    {
        // Basic validation
        return $this->quantity > 0 &&
               ($this->source_warehouse_id || $this->target_warehouse_id);
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'executed_at' => 'datetime',
        ];
    }
}
