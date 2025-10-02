<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum InventoryType: string implements HasDescription, HasLabel
{
    case FULL = 'full';
    case CYCLE = 'cycle';
    case SPOT = 'spot';
    case ANNUAL = 'annual';

    public function getLabel(): string
    {
        return match ($this) {
            self::FULL => __('Full Inventory'),
            self::CYCLE => __('Cycle Count'),
            self::SPOT => __('Spot Check'),
            self::ANNUAL => __('Annual Inventory'),
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::FULL => __('Complete inventory count of all items'),
            self::CYCLE => __('Periodic count of specific items or categories'),
            self::SPOT => __('Random or targeted count of select items'),
            self::ANNUAL => __('Yearly comprehensive inventory count'),
        };
    }
}
