<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InventoryValuationMethod: string implements HasLabel
{
    case FIFO = 'fifo';
    case LIFO = 'lifo';
    case WEIGHTED_AVERAGE = 'weighted_average';
    case STANDARD_COST = 'standard_cost';

    public function getLabel(): string
    {
        return match ($this) {
            self::FIFO => __('FIFO (First In, First Out)'),
            self::LIFO => __('LIFO (Last In, First Out)'),
            self::WEIGHTED_AVERAGE => __('Weighted Average Cost'),
            self::STANDARD_COST => __('Standard Cost'),
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::FIFO => __('Values inventory using the cost of the oldest items first'),
            self::LIFO => __('Values inventory using the cost of the newest items first'),
            self::WEIGHTED_AVERAGE => __('Values inventory using the weighted average cost of all items'),
            self::STANDARD_COST => __('Values inventory using a predetermined standard cost'),
        };
    }
}
