<?php

declare(strict_types=1);

namespace App\Enums;

enum MovementStatus: string
{
    case PLANNED = 'PLANNED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::PLANNED => 'Planned',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PLANNED => 'gray',
            self::IN_PROGRESS => 'yellow',
            self::COMPLETED => 'green',
            self::CANCELLED => 'red',
        };
    }
}