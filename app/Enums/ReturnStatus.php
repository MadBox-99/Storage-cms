<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ReturnStatus: string implements HasColor, HasLabel
{
    case DRAFT = 'draft';
    case PENDING_INSPECTION = 'pending_inspection';
    case INSPECTED = 'inspected';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case PROCESSED = 'processed';
    case RESTOCKED = 'restocked';
    case REFUNDED = 'refunded';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => __('Draft'),
            self::PENDING_INSPECTION => __('Pending Inspection'),
            self::INSPECTED => __('Inspected'),
            self::APPROVED => __('Approved'),
            self::REJECTED => __('Rejected'),
            self::PROCESSED => __('Processed'),
            self::RESTOCKED => __('Restocked'),
            self::REFUNDED => __('Refunded'),
            self::CANCELLED => __('Cancelled'),
        };
    }

    public function getColor(): string
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
