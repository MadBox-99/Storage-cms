<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ProductStatus: string implements HasColor, HasLabel
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case DISCONTINUED = 'discontinued';
    case OUT_OF_STOCK = 'out_of_stock';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::ACTIVE => __('Active'),
            self::INACTIVE => __('Inactive'),
            self::DISCONTINUED => __('Discontinued'),
            self::OUT_OF_STOCK => __('Out of Stock'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ACTIVE => Color::Green,
            self::INACTIVE => Color::Gray,
            self::DISCONTINUED => Color::Red,
            self::OUT_OF_STOCK => Color::Orange,
        };
    }
}
