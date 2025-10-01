<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum IntrastatStatus: string implements HasColor, HasLabel
{
    case DRAFT = 'draft';
    case READY = 'ready';
    case SUBMITTED = 'submitted';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => __('Draft'),
            self::READY => __('Ready for submission'),
            self::SUBMITTED => __('Submitted'),
            self::ACCEPTED => __('Accepted'),
            self::REJECTED => __('Rejected'),
            self::CANCELLED => __('Cancelled'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::READY => 'warning',
            self::SUBMITTED => 'info',
            self::ACCEPTED => 'success',
            self::REJECTED => 'danger',
            self::CANCELLED => 'gray',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::DRAFT;
    }
}
