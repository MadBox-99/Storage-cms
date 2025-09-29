<?php

declare(strict_types=1);

namespace App\Enums;

enum QualityStatus: string
{
    case PENDING_CHECK = 'PENDING_CHECK';
    case PASSED = 'PASSED';
    case FAILED = 'FAILED';
    case CONDITIONAL = 'CONDITIONAL';
    case QUARANTINE = 'QUARANTINE';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_CHECK => 'Pending Check',
            self::PASSED => 'Passed',
            self::FAILED => 'Failed',
            self::CONDITIONAL => 'Conditional',
            self::QUARANTINE => 'Quarantine',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING_CHECK => 'gray',
            self::PASSED => 'green',
            self::FAILED => 'red',
            self::CONDITIONAL => 'yellow',
            self::QUARANTINE => 'orange',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING_CHECK => 'clock',
            self::PASSED => 'check-circle',
            self::FAILED => 'x-circle',
            self::CONDITIONAL => 'exclamation-triangle',
            self::QUARANTINE => 'shield-exclamation',
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