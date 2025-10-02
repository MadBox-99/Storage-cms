<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MovementStatus;
use App\Models\Stock;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

final class StockMovementService
{
    public function execute(StockMovement $movement): void
    {
        DB::transaction(function () use ($movement): void {
            $this->validate($movement);

            if ($movement->source_warehouse_id) {
                $this->processSourceWarehouse($movement);
            }

            if ($movement->target_warehouse_id) {
                $this->processTargetWarehouse($movement);
            }

            $this->markAsCompleted($movement);
        });
    }

    public function cancel(StockMovement $movement): void
    {
        $movement->update(['status' => MovementStatus::CANCELLED]);
    }

    private function validate(StockMovement $movement): void
    {
        if ($movement->quantity <= 0) {
            throw new Exception('Invalid quantity: must be greater than zero');
        }

        if (! $movement->source_warehouse_id && ! $movement->target_warehouse_id) {
            throw new Exception('Invalid movement: must have source or target warehouse');
        }

        if ($movement->status === MovementStatus::COMPLETED) {
            throw new Exception('Movement already completed');
        }

        if ($movement->status === MovementStatus::CANCELLED) {
            throw new Exception('Cannot execute cancelled movement');
        }
    }

    private function processSourceWarehouse(StockMovement $movement): void
    {
        $sourceStock = Stock::query()
            ->where('product_id', $movement->product_id)
            ->where('warehouse_id', $movement->source_warehouse_id)
            ->first();

        if (! $sourceStock || $sourceStock->getAvailableQuantity() < $movement->quantity) {
            throw new Exception('Insufficient stock in source warehouse');
        }

        $sourceStock->decrement('quantity', $movement->quantity);
    }

    private function processTargetWarehouse(StockMovement $movement): void
    {
        $targetStock = Stock::query()->firstOrCreate([
            'product_id' => $movement->product_id,
            'warehouse_id' => $movement->target_warehouse_id,
        ], [
            'quantity' => 0,
        ]);

        $targetStock->increment('quantity', $movement->quantity);
    }

    private function markAsCompleted(StockMovement $movement): void
    {
        $movement->update([
            'status' => MovementStatus::COMPLETED,
            'executed_at' => now(),
        ]);
    }
}
