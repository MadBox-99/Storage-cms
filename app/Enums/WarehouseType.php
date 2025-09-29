<?php

declare(strict_types=1);

namespace App\Enums;

enum WarehouseType: string
{
    case MAIN = 'MAIN';
    case DISTRIBUTION = 'DISTRIBUTION';
    case RETAIL = 'RETAIL';
    case RETURN = 'RETURN';
    case QUARANTINE = 'QUARANTINE';

    public function label(): string
    {
        return match ($this) {
            self::MAIN => 'Main Warehouse',
            self::DISTRIBUTION => 'Distribution Center',
            self::RETAIL => 'Retail Store',
            self::RETURN => 'Return Center',
            self::QUARANTINE => 'Quarantine',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::MAIN => 'building-office',
            self::DISTRIBUTION => 'truck',
            self::RETAIL => 'shopping-cart',
            self::RETURN => 'arrow-uturn-left',
            self::QUARANTINE => 'exclamation-triangle',
        };
    }
}