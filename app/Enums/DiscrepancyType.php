<?php

declare(strict_types=1);

namespace App\Enums;

enum DiscrepancyType: string
{
    case SHORTAGE = 'SHORTAGE';
    case OVERAGE = 'OVERAGE';
    case MATCH = 'MATCH';

    public function label(): string
    {
        return match ($this) {
            self::SHORTAGE => 'Shortage',
            self::OVERAGE => 'Overage',
            self::MATCH => 'Match',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SHORTAGE => 'danger',
            self::OVERAGE => 'warning',
            self::MATCH => 'success',
        };
    }
}
