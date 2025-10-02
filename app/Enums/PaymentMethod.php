<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
    case CREDIT_CARD = 'credit_card';
    case CHECK = 'check';
    case OTHER = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::CASH => __('Cash'),
            self::BANK_TRANSFER => __('Bank Transfer'),
            self::CREDIT_CARD => __('Credit Card'),
            self::CHECK => __('Check'),
            self::OTHER => __('Other'),
        };
    }
}
