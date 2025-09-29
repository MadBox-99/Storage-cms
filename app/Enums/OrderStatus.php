<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case DRAFT = 'DRAFT';
    case CONFIRMED = 'CONFIRMED';
    case PROCESSING = 'PROCESSING';
    case SHIPPED = 'SHIPPED';
    case DELIVERED = 'DELIVERED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::CONFIRMED => 'Confirmed',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::CONFIRMED => 'blue',
            self::PROCESSING => 'yellow',
            self::SHIPPED => 'indigo',
            self::DELIVERED => 'green',
            self::CANCELLED => 'red',
        };
    }

    public function isEditable(): bool
    {
        return match ($this) {
            self::DRAFT, self::CONFIRMED => true,
            default => false,
        };
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::DELIVERED, self::CANCELLED => true,
            default => false,
        };
    }
}