<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum WarehouseType: string implements HasLabel
{
    case MAIN = 'main';
    case DISTRIBUTION = 'distribution';
    case RETAIL = 'retail';
    case RETURN = 'return';
    case QUARANTINE = 'quarantine';

    public function getLabel(): string
    {
        return match ($this) {
            self::MAIN => __('Main Warehouse'),
            self::DISTRIBUTION => __('Distribution Center'),
            self::RETAIL => __('Retail Store'),
            self::RETURN => __('Return Center'),
            self::QUARANTINE => __('Quarantine'),
        };
    }
}
