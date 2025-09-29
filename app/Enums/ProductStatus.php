<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductStatus: string
{
    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
    case DISCONTINUED = 'DISCONTINUED';
    case OUT_OF_STOCK = 'OUT_OF_STOCK';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::DISCONTINUED => 'Discontinued',
            self::OUT_OF_STOCK => 'Out of Stock',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'green',
            self::INACTIVE => 'gray',
            self::DISCONTINUED => 'red',
            self::OUT_OF_STOCK => 'orange',
        };
    }
}