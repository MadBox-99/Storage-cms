<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum StockStatus: string implements HasColor, HasLabel
{
    case AVAILABLE = 'available';
    case RESERVED = 'reserved';
    case DAMAGED = 'damaged';
    case QUARANTINE = 'quarantine';
    case IN_TRANSIT = 'in_transit';
    case EXPIRED = 'expired';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::AVAILABLE => __('Available'),
            self::RESERVED => __('Reserved'),
            self::DAMAGED => __('Damaged'),
            self::QUARANTINE => __('Quarantine'),
            self::IN_TRANSIT => __('In Transit'),
            self::EXPIRED => __('Expired'),
            self::COMPLETED => __('Completed'),
            self::CANCELLED => __('Cancelled'),
        };
    }

    public function getColor(): string
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
