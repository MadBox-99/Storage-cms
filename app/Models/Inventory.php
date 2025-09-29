<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Inventory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'inventory_number',
        'warehouse_id',
        'conducted_by',
        'inventory_date',
        'status',
        'type',
        'variance_value',
        'notes',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function conductedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'conducted_by');
    }

    public function inventoryLines(): HasMany
    {
        return $this->hasMany(InventoryLine::class);
    }

    public function addLine(InventoryLine $line): void
    {
        $this->inventoryLines()->save($line);
        $this->calculateVariance();
    }

    public function removeLine(InventoryLine $line): void
    {
        $line->delete();
        $this->calculateVariance();
    }

    public function calculateVariance(): void
    {
        $variance = $this->inventoryLines->sum(function ($line): int|float {
            return ($line->actual_quantity - $line->system_quantity) * $line->unit_cost;
        });

        $this->update(['variance_value' => $variance]);
    }

    public function complete(): void
    {
        $this->update(['status' => 'COMPLETED']);
    }

    public function approve(): void
    {
        $this->update(['status' => 'APPROVED']);
    }

    public function hasVariances(): bool
    {
        return $this->variance_value !== 0;
    }

    public function getVarianceCount(): int
    {
        return $this->inventoryLines->where('actual_quantity', '!=', 'system_quantity')->count();
    }

    protected function casts(): array
    {
        return [
            'inventory_date' => 'date',
            'variance_value' => 'decimal:2',
        ];
    }
}
