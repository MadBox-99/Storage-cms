<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ReturnReason: string implements HasLabel
{
    case DEFECTIVE = 'defective';
    case DAMAGED = 'damaged';
    case WRONG_ITEM = 'wrong_item';
    case NOT_AS_DESCRIBED = 'not_as_described';
    case QUALITY_ISSUE = 'quality_issue';
    case EXPIRED = 'expired';
    case OVERSTOCKED = 'overstocked';
    case CUSTOMER_CHANGE_OF_MIND = 'customer_change_of_mind';
    case OTHER = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::DEFECTIVE => __('Defective'),
            self::DAMAGED => __('Damaged'),
            self::WRONG_ITEM => __('Wrong Item'),
            self::NOT_AS_DESCRIBED => __('Not as Described'),
            self::QUALITY_ISSUE => __('Quality Issue'),
            self::EXPIRED => __('Expired'),
            self::OVERSTOCKED => __('Overstocked'),
            self::CUSTOMER_CHANGE_OF_MIND => __('Customer Change of Mind'),
            self::OTHER => __('Other'),
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
