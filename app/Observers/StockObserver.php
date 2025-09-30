<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Stock;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Notifications\OverstockAlert;
use Illuminate\Support\Facades\Notification;

final class StockObserver
{
    public function updated(Stock $stock): void
    {
        if ($stock->wasChanged('quantity')) {
            $this->checkStockLevels($stock);
        }
    }

    public function created(Stock $stock): void
    {
        $this->checkStockLevels($stock);
    }

    private function checkStockLevels(Stock $stock): void
    {
        $users = User::query()->where('is_super_admin', true)->get();

        if ($stock->isLowStock() && $stock->quantity > 0) {
            Notification::send($users, new LowStockAlert($stock));
        }

        if ($stock->quantity > $stock->maximum_stock) {
            Notification::send($users, new OverstockAlert($stock));
        }
    }
}
