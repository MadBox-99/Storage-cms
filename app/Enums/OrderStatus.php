<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum OrderStatus: string implements HasColor, HasLabel
{
    case DRAFT = 'draft';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case COMPLETED = 'completed';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::DRAFT => __('Draft'),
            self::CONFIRMED => __('Confirmed'),
            self::PROCESSING => __('Processing'),
            self::SHIPPED => __('Shipped'),
            self::COMPLETED => __('Completed'),
            self::DELIVERED => __('Delivered'),
            self::CANCELLED => __('Cancelled'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::CONFIRMED => 'blue',
            self::PROCESSING => 'yellow',
            self::SHIPPED => 'indigo',
            self::COMPLETED => 'teal',
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
            self::DELIVERED, self::CANCELLED, self::COMPLETED => true,
            default => false,
        };
    }
}
