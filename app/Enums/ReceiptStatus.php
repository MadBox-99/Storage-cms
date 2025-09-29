<?php

declare(strict_types=1);

namespace App\Enums;

enum ReceiptStatus: string
{
    case PENDING = 'PENDING';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case REJECTED = 'REJECTED';
    case CONFIRMED = 'CONFIRMED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::REJECTED => 'Rejected',
            self::CONFIRMED => 'Confirmed',
        };
    }

    public function color(): string
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
