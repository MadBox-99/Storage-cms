<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum IntrastatDirection: string implements HasColor, HasLabel
{
    case ARRIVAL = 'arrival';     // Érkezés (beszerzés más EU tagállamból)
    case DISPATCH = 'dispatch';   // Feladás (értékesítés más EU tagállamba)

    public function getLabel(): string
    {
        return match ($this) {
            self::ARRIVAL => __('Arrival (Import from EU)'),
            self::DISPATCH => __('Dispatch (Export to EU)'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ARRIVAL => 'info',
            self::DISPATCH => 'success',
        };
    }
}
