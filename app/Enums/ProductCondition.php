<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductCondition: string
{
    case GOOD = 'GOOD';
    case MINOR_DAMAGE = 'MINOR_DAMAGE';
    case DAMAGED = 'DAMAGED';
    case DEFECTIVE = 'DEFECTIVE';
    case EXPIRED = 'EXPIRED';

    public function label(): string
    {
        return match ($this) {
            self::GOOD => 'Good',
            self::MINOR_DAMAGE => 'Minor Damage',
            self::DAMAGED => 'Damaged',
            self::DEFECTIVE => 'Defective',
            self::EXPIRED => 'Expired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::GOOD => 'success',
            self::MINOR_DAMAGE => 'warning',
            self::DAMAGED => 'danger',
            self::DEFECTIVE => 'danger',
            self::EXPIRED => 'danger',
        };
    }

    public function canBeRestocked(): bool
    {
        return match ($this) {
            self::GOOD, self::MINOR_DAMAGE => true,
            default => false,
        };
    }

    public function requiresDisposal(): bool
    {
        return match ($this) {
            self::DAMAGED, self::DEFECTIVE, self::EXPIRED => true,
            default => false,
        };
    }
}
