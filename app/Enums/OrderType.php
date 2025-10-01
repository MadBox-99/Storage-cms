<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OrderType: string implements HasLabel
{
    case PURCHASE = 'purchase';
    case SALES = 'sales';
    case SALE = 'sale';
    case TRANSFER = 'transfer';
    case RETURN = 'return';

    public function getLabel(): string
    {
        return match ($this) {
            self::PURCHASE => __('Purchase Order'),
            self::SALES => __('Sales Order'),
            self::SALE => __('Sale Order'),
            self::TRANSFER => __('Transfer Order'),
            self::RETURN => __('Return Order'),
        };
    }

    public function requiresCustomer(): bool
    {
        return match ($this) {
            self::SALES, self::RETURN => true,
            self::PURCHASE, self::TRANSFER => false,
        };
    }

    public function requiresSupplier(): bool
    {
        return match ($this) {
            self::PURCHASE, self::RETURN => true,
            self::SALES, self::TRANSFER => false,
        };
    }
}
