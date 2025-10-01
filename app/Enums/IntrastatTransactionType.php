<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum IntrastatTransactionType: string implements HasLabel
{
    case OUTRIGHT_PURCHASE_SALE = '11';           // Egyszerű adásvétel
    case RETURN_REPLACEMENT = '21';                // Visszáru, csere
    case FREE_OF_CHARGE = '31';                    // Ingyenes szállítás
    case PROCESSING_UNDER_CONTRACT = '41';         // Bérmunka
    case PROCESSING_AFTER_PROCESSING = '51';       // Bérmunka utáni visszaszállítás
    case LEASING = '61';                           // Lízing
    case MILITARY_EQUIPMENT = '71';                // Katonai felszerelés
    case CONSTRUCTION_MATERIALS = '81';            // Építőanyag projekthelyekre
    case OTHER = '99';                             // Egyéb

    public function getLabel(): string
    {
        return match ($this) {
            self::OUTRIGHT_PURCHASE_SALE => __('Outright purchase/sale'),
            self::RETURN_REPLACEMENT => __('Return/Replacement'),
            self::FREE_OF_CHARGE => __('Free of charge'),
            self::PROCESSING_UNDER_CONTRACT => __('Processing under contract'),
            self::PROCESSING_AFTER_PROCESSING => __('Return after processing'),
            self::LEASING => __('Leasing'),
            self::MILITARY_EQUIPMENT => __('Military equipment'),
            self::CONSTRUCTION_MATERIALS => __('Construction materials'),
            self::OTHER => __('Other'),
        };
    }
}
