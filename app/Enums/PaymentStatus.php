<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasLabel
{
    case PENDING = 'PENDING';
    case PROCESSING = 'PROCESSING';
    case COMPLETED = 'COMPLETED';
    case FAILED = 'FAILED';
    case REFUNDED = 'REFUNDED';
    case CANCELLED = 'CANCELLED';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => __('Pending'),
            self::PROCESSING => __('Processing'),
            self::COMPLETED => __('Completed'),
            self::FAILED => __('Failed'),
            self::REFUNDED => __('Refunded'),
            self::CANCELLED => __('Cancelled'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PROCESSING => 'info',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
            self::REFUNDED => 'gray',
            self::CANCELLED => 'gray',
        };
    }
}
