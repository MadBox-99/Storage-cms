<?php

declare(strict_types=1);

namespace App\Enums;

use BackedEnum;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum SupplierRating: string implements HasColor, HasIcon, HasLabel
{
    case EXCELLENT = 'excellent';
    case GOOD = 'good';
    case AVERAGE = 'average';
    case POOR = 'poor';
    case BLACKLISTED = 'blacklisted';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::EXCELLENT => __('Excellent'),
            self::GOOD => __('Good'),
            self::AVERAGE => __('Average'),
            self::POOR => __('Poor'),
            self::BLACKLISTED => __('Blacklisted'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::EXCELLENT => Color::Green,
            self::GOOD => Color::Blue,
            self::AVERAGE => Color::Yellow,
            self::POOR => Color::Orange,
            self::BLACKLISTED => Color::Red,
        };
    }

    public function getIcon(): string|BackedEnum|null
    {
        return match ($this) {
            self::EXCELLENT => Heroicon::Star,
            self::GOOD => Heroicon::HandThumbUp,
            self::AVERAGE => Heroicon::Minus,
            self::POOR => Heroicon::HandThumbDown,
            self::BLACKLISTED => Heroicon::XMark,
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
        return ! $this->isBlacklisted();
    }

    public function canDoBusinessWith(): bool
    {
        return in_array($this, [self::EXCELLENT, self::GOOD, self::AVERAGE]);
    }
}
