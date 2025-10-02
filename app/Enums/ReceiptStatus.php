<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ReceiptStatus: string implements HasColor, HasLabel
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
    case CONFIRMED = 'confirmed';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => __('Pending'),
            self::IN_PROGRESS => __('In Progress'),
            self::COMPLETED => __('Completed'),
            self::REJECTED => __('Rejected'),
            self::CONFIRMED => __('Confirmed'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::IN_PROGRESS => 'blue',
            self::COMPLETED => 'green',
            self::REJECTED => 'red',
            self::CONFIRMED => 'orange',
        };
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }

    public function canTransitionTo(self $status): bool
    {
        return match ($this) {
            self::PENDING => in_array($status, [self::IN_PROGRESS, self::REJECTED]),
            self::IN_PROGRESS => in_array($status, [self::COMPLETED, self::REJECTED]),
            self::COMPLETED => false,
            self::REJECTED => false,
            self::CONFIRMED => false,
        };
    }
}
