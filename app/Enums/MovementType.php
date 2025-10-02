<?php

declare(strict_types=1);

namespace App\Enums;

enum MovementType: string
{
    case INBOUND = 'inbound';
    case OUTBOUND = 'outbound';
    case TRANSFER = 'transfer';
    case ADJUSTMENT = 'adjustment';
    case RETURN = 'return';

    public function label(): string
    {
        return match ($this) {
            self::INBOUND => 'Inbound',
            self::OUTBOUND => 'Outbound',
            self::TRANSFER => 'Transfer',
            self::ADJUSTMENT => 'Adjustment',
            self::RETURN => 'Return',
        };
    }

    public function isAdditive(): bool
    {
        return match ($this) {
            self::INBOUND, self::RETURN => true,
            self::OUTBOUND => false,
            self::TRANSFER, self::ADJUSTMENT => false,
        };
    }
}
