<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum IntrastatTransportMode: string implements HasLabel
{
    case SEA = '1';              // Tengeri
    case RAIL = '2';             // Vasúti
    case ROAD = '3';             // Közúti
    case AIR = '4';              // Légi
    case MAIL = '5';             // Postai
    case MULTIMODAL = '7';       // Kombinált (többféle)
    case INLAND_WATERWAY = '8';  // Belvízi
    case SELF_PROPULSION = '9';  // Saját erőből (járművek)

    public function getLabel(): string
    {
        return match ($this) {
            self::SEA => __('Sea transport'),
            self::RAIL => __('Rail transport'),
            self::ROAD => __('Road transport'),
            self::AIR => __('Air transport'),
            self::MAIL => __('Postal consignment'),
            self::MULTIMODAL => __('Multimodal transport'),
            self::INLAND_WATERWAY => __('Inland waterway transport'),
            self::SELF_PROPULSION => __('Self-propulsion'),
        };
    }
}
