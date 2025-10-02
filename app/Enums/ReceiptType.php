<?php

declare(strict_types=1);

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum ReceiptType: string implements HasColor, HasIcon, HasLabel
{
    case PURCHASE_ORDER = 'purchase_order';
    case RETURN = 'return';
    case TRANSFER = 'transfer';
    case ADJUSTMENT = 'adjustment';
    case PRODUCTION = 'production';

    public function getLabel(): string
    {
        return match ($this) {
            self::PURCHASE_ORDER => __('Purchase Order'),
            self::RETURN => __('Return'),
            self::TRANSFER => __('Transfer'),
            self::ADJUSTMENT => __('Adjustment'),
            self::PRODUCTION => __('Production'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PURCHASE_ORDER => 'blue',
            self::RETURN => 'orange',
            self::TRANSFER => 'purple',
            self::ADJUSTMENT => 'yellow',
            self::PRODUCTION => 'green',
        };
    }

    public function getIcon(): string|BackedEnum|null
    {
        return match ($this) {
            self::PURCHASE_ORDER => Heroicon::ShoppingCart,
            self::RETURN => Heroicon::ArrowUturnLeft,
            self::TRANSFER => Heroicon::ArrowRight,
            self::ADJUSTMENT => Heroicon::AdjustmentsHorizontal,
            self::PRODUCTION => Heroicon::WrenchScrewdriver,
        };
    }

    public function requiresOrder(): bool
    {
        return in_array($this, [self::PURCHASE_ORDER, self::RETURN]);
    }

    public function requiresWarehouse(): bool
    {
        return true; // All receipt types require a warehouse
    }
}
