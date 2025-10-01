<?php

declare(strict_types=1);

namespace App\Enums;

enum InventoryStatus: string
{
    case DRAFT = 'DRAFT';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case APPROVED = 'APPROVED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::APPROVED => 'Approved',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
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
