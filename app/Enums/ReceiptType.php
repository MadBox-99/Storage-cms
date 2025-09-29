<?php

declare(strict_types=1);

namespace App\Enums;

enum ReceiptType: string
{
    case PURCHASE_ORDER = 'PURCHASE_ORDER';
    case RETURN = 'RETURN';
    case TRANSFER = 'TRANSFER';
    case ADJUSTMENT = 'ADJUSTMENT';
    case PRODUCTION = 'PRODUCTION';

    public function label(): string
    {
        return match ($this) {
            self::PURCHASE_ORDER => 'Purchase Order',
            self::RETURN => 'Return',
            self::TRANSFER => 'Transfer',
            self::ADJUSTMENT => 'Adjustment',
            self::PRODUCTION => 'Production',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PURCHASE_ORDER => 'blue',
            self::RETURN => 'orange',
            self::TRANSFER => 'purple',
            self::ADJUSTMENT => 'yellow',
            self::PRODUCTION => 'green',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PURCHASE_ORDER => 'shopping-cart',
            self::RETURN => 'arrow-uturn-left',
            self::TRANSFER => 'arrow-right-arrow-left',
            self::ADJUSTMENT => 'adjustments-horizontal',
            self::PRODUCTION => 'wrench-screwdriver',
        };
    }

    public function requiresOrder(): bool
    {
        return in_array($this, [self::PURCHASE_ORDER, self::RETURN]);
    }

    public function requiresWarehouse(): bool
    {
        return true; // All receipt types require a warehouse
    }
}