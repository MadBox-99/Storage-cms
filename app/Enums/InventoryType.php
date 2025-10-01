<?php

declare(strict_types=1);

namespace App\Enums;

enum InventoryType: string
{
    case FULL = 'FULL';
    case CYCLE = 'CYCLE';
    case SPOT = 'SPOT';

    public function label(): string
    {
        return match ($this) {
            self::FULL => 'Full Inventory',
            self::CYCLE => 'Cycle Count',
            self::SPOT => 'Spot Check',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::FULL => 'Complete inventory count of all items',
            self::CYCLE => 'Periodic count of specific items or categories',
            self::SPOT => 'Random or targeted count of select items',
        };
    }
}
