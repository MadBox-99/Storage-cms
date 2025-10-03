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
    case LOW_STOCK = 'low_stock';
    case OUT_OF_STOCK = 'out_of_stock';
    case IN_STOCK = 'in_stock';

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
            self::LOW_STOCK => __('Low Stock'),
            self::OUT_OF_STOCK => __('Out of Stock'),
            self::IN_STOCK => __('In Stock'),
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
            self::COMPLETED => 'gray',
            self::CANCELLED => 'gray',
            self::LOW_STOCK => 'yellow',
            self::OUT_OF_STOCK => 'red',
            self::IN_STOCK => 'green',
        };
    }

    public function isUsable(): bool
    {
        return match ($this) {
            self::AVAILABLE => true,
            self::IN_STOCK => true,
            default => false,
        };
    }
}
