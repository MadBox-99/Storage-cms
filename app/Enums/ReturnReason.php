<?php

declare(strict_types=1);

namespace App\Enums;

enum ReturnReason: string
{
    case DEFECTIVE = 'DEFECTIVE';
    case DAMAGED = 'DAMAGED';
    case WRONG_ITEM = 'WRONG_ITEM';
    case NOT_AS_DESCRIBED = 'NOT_AS_DESCRIBED';
    case QUALITY_ISSUE = 'QUALITY_ISSUE';
    case EXPIRED = 'EXPIRED';
    case OVERSTOCKED = 'OVERSTOCKED';
    case CUSTOMER_CHANGE_OF_MIND = 'CUSTOMER_CHANGE_OF_MIND';
    case OTHER = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::DEFECTIVE => 'Defective',
            self::DAMAGED => 'Damaged',
            self::WRONG_ITEM => 'Wrong Item',
            self::NOT_AS_DESCRIBED => 'Not as Described',
            self::QUALITY_ISSUE => 'Quality Issue',
            self::EXPIRED => 'Expired',
            self::OVERSTOCKED => 'Overstocked',
            self::CUSTOMER_CHANGE_OF_MIND => 'Customer Change of Mind',
            self::OTHER => 'Other',
        };
    }

    public function requiresInspection(): bool
    {
        return match ($this) {
            self::DEFECTIVE, self::DAMAGED, self::QUALITY_ISSUE, self::EXPIRED => true,
            default => false,
        };
    }
}
