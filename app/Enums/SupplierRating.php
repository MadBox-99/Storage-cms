<?php

declare(strict_types=1);

namespace App\Enums;

enum SupplierRating: string
{
    case EXCELLENT = 'EXCELLENT';
    case GOOD = 'GOOD';
    case AVERAGE = 'AVERAGE';
    case POOR = 'POOR';
    case BLACKLISTED = 'BLACKLISTED';

    public function label(): string
    {
        return match ($this) {
            self::EXCELLENT => 'Excellent',
            self::GOOD => 'Good',
            self::AVERAGE => 'Average',
            self::POOR => 'Poor',
            self::BLACKLISTED => 'Blacklisted',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EXCELLENT => 'green',
            self::GOOD => 'blue',
            self::AVERAGE => 'yellow',
            self::POOR => 'orange',
            self::BLACKLISTED => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::EXCELLENT => 'star',
            self::GOOD => 'hand-thumb-up',
            self::AVERAGE => 'minus',
            self::POOR => 'hand-thumb-down',
            self::BLACKLISTED => 'x-mark',
        };
    }

    public function score(): int
    {
        return match ($this) {
            self::EXCELLENT => 5,
            self::GOOD => 4,
            self::AVERAGE => 3,
            self::POOR => 2,
            self::BLACKLISTED => 1,
        };
    }

    public function isBlacklisted(): bool
    {
        return $this === self::BLACKLISTED;
    }

    public function isAcceptable(): bool
    {
        return !$this->isBlacklisted();
    }

    public function canDoBusinessWith(): bool
    {
        return in_array($this, [self::EXCELLENT, self::GOOD, self::AVERAGE]);
    }
}