<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Enums\InventoryValuationMethod;
use App\Enums\StockTransactionType;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Models\Warehouse;
use Illuminate\Support\Collection;

final class InventoryValuationService
{
    public function calculateStockValue(Stock $stock): float
    {
        $warehouse = $stock->warehouse;
        $method = $warehouse->valuation_method;

        return match ($method) {
            InventoryValuationMethod::FIFO => $this->calculateFifoValue($stock),
            InventoryValuationMethod::LIFO => $this->calculateLifoValue($stock),
            InventoryValuationMethod::WEIGHTED_AVERAGE => $this->calculateWeightedAverageValue($stock),
            InventoryValuationMethod::STANDARD_COST => $this->calculateStandardCostValue($stock),
        };
    }

    public function calculateFifoValue(Stock $stock): float
    {
        $remainingQuantity = $stock->quantity;
        $totalValue = 0.0;

        $transactions = StockTransaction::query()
            ->where('stock_id', $stock->id)
            ->where('type', StockTransactionType::INBOUND)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($transactions as $transaction) {
            if ($remainingQuantity <= 0) {
                break;
            }

            $quantityToUse = min($transaction->remaining_quantity, $remainingQuantity);
            $totalValue += $quantityToUse * (float) $transaction->unit_cost;
            $remainingQuantity -= $quantityToUse;
        }

        return $totalValue;
    }

    public function calculateLifoValue(Stock $stock): float
    {
        $remainingQuantity = $stock->quantity;
        $totalValue = 0.0;

        $transactions = StockTransaction::query()
            ->where('stock_id', $stock->id)
            ->where('type', StockTransactionType::INBOUND)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($transactions as $transaction) {
            if ($remainingQuantity <= 0) {
                break;
            }

            $quantityToUse = min($transaction->remaining_quantity, $remainingQuantity);
            $totalValue += $quantityToUse * (float) $transaction->unit_cost;
            $remainingQuantity -= $quantityToUse;
        }

        return $totalValue;
    }

    public function calculateWeightedAverageValue(Stock $stock): float
    {
        $totalQuantity = StockTransaction::query()
            ->where('stock_id', $stock->id)
            ->where('type', StockTransactionType::INBOUND)
            ->where('remaining_quantity', '>', 0)
            ->sum('remaining_quantity');

        if ($totalQuantity === 0) {
            return 0.0;
        }

        $totalCost = StockTransaction::query()
            ->where('stock_id', $stock->id)
            ->where('type', StockTransactionType::INBOUND)
            ->where('remaining_quantity', '>', 0)
            ->get()
            ->sum(fn ($t) => $t->remaining_quantity * (float) $t->unit_cost);

        $averageCost = $totalCost / $totalQuantity;

        return $stock->quantity * $averageCost;
    }

    public function calculateStandardCostValue(Stock $stock): float
    {
        $standardCost = (float) ($stock->product->standard_cost ?? $stock->product->price ?? 0);

        return $stock->quantity * $standardCost;
    }

    public function getWarehouseTotalValue(Warehouse $warehouse): float
    {
        return $warehouse->stocks()
            ->get()
            ->sum(fn (Stock $stock) => $this->calculateStockValue($stock));
    }

    public function getProductTotalValue(Product $product): float
    {
        return $product->stocks()
            ->get()
            ->sum(fn (Stock $stock) => $this->calculateStockValue($stock));
    }

    public function getCategoryTotalValue(int $categoryId): float
    {
        $products = Product::query()->where('category_id', $categoryId)->get();

        return $products->sum(fn (Product $product) => $this->getProductTotalValue($product));
    }

    public function recordStockIn(Stock $stock, int $quantity, float $unitCost, ?string $referenceType = null, ?int $referenceId = null, ?string $notes = null): StockTransaction
    {
        $transaction = StockTransaction::create([
            'stock_id' => $stock->id,
            'product_id' => $stock->product_id,
            'warehouse_id' => $stock->warehouse_id,
            'type' => StockTransactionType::INBOUND,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $quantity * $unitCost,
            'remaining_quantity' => $quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
        ]);

        $stock->increment('quantity', $quantity);
        $this->updateStockValue($stock);

        return $transaction;
    }

    public function recordStockOut(Stock $stock, int $quantity, ?string $referenceType = null, ?int $referenceId = null, ?string $notes = null): Collection
    {
        $warehouse = $stock->warehouse;
        $method = $warehouse->valuation_method;

        $transactions = match ($method) {
            InventoryValuationMethod::FIFO => $this->consumeFifo($stock, $quantity, $referenceType, $referenceId, $notes),
            InventoryValuationMethod::LIFO => $this->consumeLifo($stock, $quantity, $referenceType, $referenceId, $notes),
            InventoryValuationMethod::WEIGHTED_AVERAGE, InventoryValuationMethod::STANDARD_COST => $this->consumeAverage($stock, $quantity, $referenceType, $referenceId, $notes),
        };

        $stock->decrement('quantity', $quantity);
        $this->updateStockValue($stock);

        return $transactions;
    }

    private function consumeFifo(Stock $stock, int $quantity, ?string $referenceType, ?int $referenceId, ?string $notes): Collection
    {
        $remainingToConsume = $quantity;
        $transactions = collect();

        $inTransactions = StockTransaction::query()
            ->where('stock_id', $stock->id)
            ->where('type', StockTransactionType::INBOUND)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($inTransactions as $inTransaction) {
            if ($remainingToConsume <= 0) {
                break;
            }

            $consumeQty = min($inTransaction->remaining_quantity, $remainingToConsume);

            $outTransaction = StockTransaction::create([
                'stock_id' => $stock->id,
                'product_id' => $stock->product_id,
                'warehouse_id' => $stock->warehouse_id,
                'type' => StockTransactionType::OUTBOUND,
                'quantity' => $consumeQty,
                'unit_cost' => $inTransaction->unit_cost,
                'total_cost' => $consumeQty * (float) $inTransaction->unit_cost,
                'remaining_quantity' => 0,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
            ]);

            $inTransaction->decrement('remaining_quantity', $consumeQty);
            $remainingToConsume -= $consumeQty;
            $transactions->push($outTransaction);
        }

        return $transactions;
    }

    private function consumeLifo(Stock $stock, int $quantity, ?string $referenceType, ?int $referenceId, ?string $notes): Collection
    {
        $remainingToConsume = $quantity;
        $transactions = collect();

        $inTransactions = StockTransaction::query()
            ->where('stock_id', $stock->id)
            ->where('type', StockTransactionType::INBOUND)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($inTransactions as $inTransaction) {
            if ($remainingToConsume <= 0) {
                break;
            }

            $consumeQty = min($inTransaction->remaining_quantity, $remainingToConsume);

            $outTransaction = StockTransaction::create([
                'stock_id' => $stock->id,
                'product_id' => $stock->product_id,
                'warehouse_id' => $stock->warehouse_id,
                'type' => StockTransactionType::OUTBOUND,
                'quantity' => $consumeQty,
                'unit_cost' => $inTransaction->unit_cost,
                'total_cost' => $consumeQty * (float) $inTransaction->unit_cost,
                'remaining_quantity' => 0,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
            ]);

            $inTransaction->decrement('remaining_quantity', $consumeQty);
            $remainingToConsume -= $consumeQty;
            $transactions->push($outTransaction);
        }

        return $transactions;
    }

    private function consumeAverage(Stock $stock, int $quantity, ?string $referenceType, ?int $referenceId, ?string $notes): Collection
    {
        $totalQuantity = StockTransaction::query()
            ->where('stock_id', $stock->id)
            ->where('type', StockTransactionType::INBOUND)
            ->where('remaining_quantity', '>', 0)
            ->sum('remaining_quantity');

        if ($totalQuantity === 0) {
            return collect();
        }

        $totalCost = StockTransaction::query()
            ->where('stock_id', $stock->id)
            ->where('type', StockTransactionType::INBOUND)
            ->where('remaining_quantity', '>', 0)
            ->get()
            ->sum(fn ($t) => $t->remaining_quantity * (float) $t->unit_cost);

        $averageCost = $totalCost / $totalQuantity;

        $outTransaction = StockTransaction::create([
            'stock_id' => $stock->id,
            'product_id' => $stock->product_id,
            'warehouse_id' => $stock->warehouse_id,
            'type' => StockTransactionType::OUTBOUND,
            'quantity' => $quantity,
            'unit_cost' => $averageCost,
            'total_cost' => $quantity * $averageCost,
            'remaining_quantity' => 0,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
        ]);

        $inTransactions = StockTransaction::query()
            ->where('stock_id', $stock->id)
            ->where('type', StockTransactionType::INBOUND)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        $remainingToConsume = $quantity;
        foreach ($inTransactions as $inTransaction) {
            if ($remainingToConsume <= 0) {
                break;
            }

            $consumeQty = min($inTransaction->remaining_quantity, $remainingToConsume);
            $inTransaction->decrement('remaining_quantity', $consumeQty);
            $remainingToConsume -= $consumeQty;
        }

        return collect([$outTransaction]);
    }

    private function updateStockValue(Stock $stock): void
    {
        $value = $this->calculateStockValue($stock);
        $unitCost = $stock->quantity > 0 ? $value / $stock->quantity : 0;

        $stock->update([
            'total_value' => $value,
            'unit_cost' => $unitCost,
        ]);
    }
}
