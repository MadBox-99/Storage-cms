<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum InvoiceStatus: string implements HasColor, HasLabel
{
    case DRAFT = 'DRAFT';
    case ISSUED = 'ISSUED';
    case SENT = 'SENT';
    case PARTIALLY_PAID = 'PARTIALLY_PAID';
    case PAID = 'PAID';
    case OVERDUE = 'OVERDUE';
    case CANCELLED = 'CANCELLED';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => __('Draft'),
            self::ISSUED => __('Issued'),
            self::SENT => __('Sent'),
            self::PARTIALLY_PAID => __('Partially Paid'),
            self::PAID => __('Paid'),
            self::OVERDUE => __('Overdue'),
            self::CANCELLED => __('Cancelled'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::ISSUED => 'info',
            self::SENT => 'primary',
            self::PARTIALLY_PAID => 'warning',
            self::PAID => 'success',
            self::OVERDUE => 'danger',
            self::CANCELLED => 'gray',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::DRAFT;
    }
}
