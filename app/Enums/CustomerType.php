<?php

declare(strict_types=1);

namespace App\Enums;

enum CustomerType: string
{
    case RETAIL = 'RETAIL';
    case WHOLESALE = 'WHOLESALE';
    case DISTRIBUTOR = 'DISTRIBUTOR';
    case INTERNAL = 'INTERNAL';
    case VIP = 'VIP';

    public function label(): string
    {
        return match ($this) {
            self::RETAIL => 'Retail Customer',
            self::WHOLESALE => 'Wholesale Customer',
            self::DISTRIBUTOR => 'Distributor',
            self::INTERNAL => 'Internal',
            self::VIP => 'VIP Customer',
        };
    }

    public function discountRate(): float
    {
        return match ($this) {
            self::RETAIL => 0.0,
            self::WHOLESALE => 5.0,
            self::DISTRIBUTOR => 10.0,
            self::INTERNAL => 0.0,
            self::VIP => 15.0,
        };
    }
}