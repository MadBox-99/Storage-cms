<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum InventoryStatus: string implements HasColor, HasLabel
{
    case DRAFT = 'draft';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case APPROVED = 'approved';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => __('Draft'),
            self::IN_PROGRESS => __('In Progress'),
            self::COMPLETED => __('Completed'),
            self::APPROVED => __('Approved'),
            self::CANCELLED => __('Cancelled'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::IN_PROGRESS => 'warning',
            self::COMPLETED => 'info',
            self::APPROVED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    public function isEditable(): bool
    {
        return match ($this) {
            self::DRAFT, self::IN_PROGRESS => true,
            default => false,
        };
    }
}
