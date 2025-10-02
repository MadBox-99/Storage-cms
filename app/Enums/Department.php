<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Department: string implements HasLabel
{
    case WAREHOUSE = 'warehouse';
    case LOGISTICS = 'logistics';
    case PROCUREMENT = 'procurement';
    case QUALITY = 'quality';
    case MANAGEMENT = 'management';
    case IT = 'it';

    public function getLabel(): string
    {
        return match ($this) {
            self::WAREHOUSE => __('Warehouse Operations'),
            self::LOGISTICS => __('Logistics'),
            self::PROCUREMENT => __('Procurement'),
            self::QUALITY => __('Quality Control'),
            self::MANAGEMENT => __('Management'),
            self::IT => __('Information Technology'),
        };
    }

    public function responsibilities(): array
    {
        return match ($this) {
            self::WAREHOUSE => ['Inventory management', 'Stock movements', 'Order fulfillment'],
            self::LOGISTICS => ['Transportation', 'Distribution', 'Route planning'],
            self::PROCUREMENT => ['Supplier management', 'Purchase orders', 'Contract negotiation'],
            self::QUALITY => ['Quality inspections', 'Standards compliance', 'Product testing'],
            self::MANAGEMENT => ['Strategic planning', 'Resource allocation', 'Performance monitoring'],
            self::IT => ['System maintenance', 'Technical support', 'Data management'],
        };
    }
}
