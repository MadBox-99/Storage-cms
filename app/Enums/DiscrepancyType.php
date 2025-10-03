<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DiscrepancyType: string implements HasColor, HasLabel
{
    case SHORTAGE = 'shortage';
    case OVERAGE = 'overage';
    case MATCH = 'match';

    public function getLabel(): string
    {
        return match ($this) {
            self::SHORTAGE => __('Shortage'),
            self::OVERAGE => __('Overage'),
            self::MATCH => __('Match'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SHORTAGE => 'danger',
            self::OVERAGE => 'warning',
            self::MATCH => 'success',
        };
    }
}
