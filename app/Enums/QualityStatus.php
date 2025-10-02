<?php

declare(strict_types=1);

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum QualityStatus: string implements HasColor, HasIcon, HasLabel
{
    case PENDING_CHECK = 'pending_check';
    case PASSED = 'passed';
    case FAILED = 'failed';
    case CONDITIONAL = 'conditional';
    case QUARANTINE = 'quarantine';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING_CHECK => __('Pending Check'),
            self::PASSED => __('Passed'),
            self::FAILED => __('Failed'),
            self::CONDITIONAL => __('Conditional'),
            self::QUARANTINE => __('Quarantine'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING_CHECK => 'gray',
            self::PASSED => 'green',
            self::FAILED => 'red',
            self::CONDITIONAL => 'yellow',
            self::QUARANTINE => 'orange',
        };
    }

    public function getIcon(): string|BackedEnum|null
    {
        return match ($this) {
            self::PENDING_CHECK => Heroicon::Clock,
            self::PASSED => Heroicon::CheckCircle,
            self::FAILED => Heroicon::XCircle,
            self::CONDITIONAL => Heroicon::ExclamationTriangle,
            self::QUARANTINE => Heroicon::ShieldExclamation,
        };
    }

    public function isPassed(): bool
    {
        return $this === self::PASSED;
    }

    public function isFailed(): bool
    {
        return $this === self::FAILED;
    }

    public function isQuarantine(): bool
    {
        return $this === self::QUARANTINE;
    }

    public function requiresAction(): bool
    {
        return in_array($this, [self::FAILED, self::QUARANTINE, self::CONDITIONAL]);
    }

    public function canProceedToStock(): bool
    {
        return in_array($this, [self::PASSED, self::CONDITIONAL]);
    }
}
