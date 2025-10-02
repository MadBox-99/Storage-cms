<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MovementStatus;
use App\Enums\MovementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function targetWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'target_warehouse_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'executed_by');
    }

    // Query scopes
    public function scopePlanned($query)
    {
        return $query->where('status', MovementStatus::PLANNED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', MovementStatus::COMPLETED);
    }

    // Helper methods
    public function isPlanned(): bool
    {
        return $this->status === MovementStatus::PLANNED;
    }

    public function isCompleted(): bool
    {
        return $this->status === MovementStatus::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === MovementStatus::CANCELLED;
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'executed_at' => 'datetime',
            'status' => MovementStatus::class,
            'type' => MovementType::class,
        ];
    }
}
