<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case CASH = 'CASH';
    case BANK_TRANSFER = 'BANK_TRANSFER';
    case CREDIT_CARD = 'CREDIT_CARD';
    case CHECK = 'CHECK';
    case OTHER = 'OTHER';

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
