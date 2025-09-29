<?php

declare(strict_types=1);

namespace App\Enums;

enum Department: string
{
    case WAREHOUSE = 'WAREHOUSE';
    case LOGISTICS = 'LOGISTICS';
    case PROCUREMENT = 'PROCUREMENT';
    case QUALITY = 'QUALITY';
    case MANAGEMENT = 'MANAGEMENT';
    case IT = 'IT';

    public function label(): string
    {
        return match ($this) {
            self::WAREHOUSE => 'Warehouse Operations',
            self::LOGISTICS => 'Logistics',
            self::PROCUREMENT => 'Procurement',
            self::QUALITY => 'Quality Control',
            self::MANAGEMENT => 'Management',
            self::IT => 'Information Technology',
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