<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum NavigationGroup: string implements HasIcon, HasLabel
{
    case INVENTORY_MANAGEMENT = 'Inventory Management';
    case SALES = 'Sales';
    case PURCHASING = 'Purchasing';
    case ADMINISTRATION = 'Administration';
    case INTRASTAT = 'Intrastat';
    case SETTINGS = 'Settings';

    public function getLabel(): string
    {
        return match ($this) {
            self::INVENTORY_MANAGEMENT => __('Inventory Management'),
            self::SALES => __('Sales'),
            self::PURCHASING => __('Purchasing'),
            self::ADMINISTRATION => __('Administration'),
            self::INTRASTAT => __('Intrastat'),
            self::SETTINGS => __('Settings'),
        };
    }

    public function getIcon(): ?Heroicon
    {
        return match ($this) {
            self::INVENTORY_MANAGEMENT => Heroicon::OutlinedCubeTransparent,
            self::SALES => Heroicon::OutlinedShoppingCart,
            self::PURCHASING => Heroicon::OutlinedTruck,
            self::ADMINISTRATION => Heroicon::OutlinedUserGroup,
            self::INTRASTAT => Heroicon::OutlinedGlobeEuropeAfrica,
            self::SETTINGS => Heroicon::OutlinedCog6Tooth,
        };
    }
}
