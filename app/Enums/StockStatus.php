<?php

declare(strict_types=1);

namespace App\Enums;

enum StockStatus: string
{
    case AVAILABLE = 'AVAILABLE';
    case RESERVED = 'RESERVED';
    case DAMAGED = 'DAMAGED';
    case QUARANTINE = 'QUARANTINE';
    case IN_TRANSIT = 'IN_TRANSIT';
    case EXPIRED = 'EXPIRED';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Available',
            self::RESERVED => 'Reserved',
            self::DAMAGED => 'Damaged',
            self::QUARANTINE => 'Quarantine',
            self::IN_TRANSIT => 'In Transit',
            self::EXPIRED => 'Expired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AVAILABLE => 'green',
            self::RESERVED => 'blue',
            self::DAMAGED => 'orange',
            self::QUARANTINE => 'purple',
            self::IN_TRANSIT => 'yellow',
            self::EXPIRED => 'red',
        };
    }

    public function isUsable(): bool
    {
        return match ($this) {
            self::AVAILABLE => true,
            default => false,
        };
    }
}