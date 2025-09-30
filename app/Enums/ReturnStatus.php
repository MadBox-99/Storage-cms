<?php

declare(strict_types=1);

namespace App\Enums;

enum ReturnStatus: string
{
    case DRAFT = 'DRAFT';
    case PENDING_INSPECTION = 'PENDING_INSPECTION';
    case INSPECTED = 'INSPECTED';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case PROCESSED = 'PROCESSED';
    case RESTOCKED = 'RESTOCKED';
    case REFUNDED = 'REFUNDED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PENDING_INSPECTION => 'Pending Inspection',
            self::INSPECTED => 'Inspected',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::PROCESSED => 'Processed',
            self::RESTOCKED => 'Restocked',
            self::REFUNDED => 'Refunded',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PENDING_INSPECTION => 'yellow',
            self::INSPECTED => 'blue',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::PROCESSED => 'info',
            self::RESTOCKED => 'success',
            self::REFUNDED => 'warning',
            self::CANCELLED => 'danger',
        };
    }

    public function isEditable(): bool
    {
        return match ($this) {
            self::DRAFT, self::PENDING_INSPECTION => true,
            default => false,
        };
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::RESTOCKED, self::REFUNDED, self::CANCELLED, self::REJECTED => true,
            default => false,
        };
    }
}
