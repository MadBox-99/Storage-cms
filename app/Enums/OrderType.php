<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderType: string
{
    case PURCHASE = 'PURCHASE';
    case SALES = 'SALES';
    case SALE = 'SALE';
    case TRANSFER = 'TRANSFER';
    case RETURN = 'RETURN';

    public function label(): string
    {
        return match ($this) {
            self::PURCHASE => 'Purchase Order',
            self::SALES => 'Sales Order',
            self::SALE => 'Sale Order',
            self::TRANSFER => 'Transfer Order',
            self::RETURN => 'Return Order',
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
