<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum IntrastatDeliveryTerms: string implements HasLabel
{
    case EXW = 'EXW';  // Ex Works - Gyárból (eladó telephelye)
    case FCA = 'FCA';  // Free Carrier - Fuvarozónak átadva
    case FAS = 'FAS';  // Free Alongside Ship - Hajó mellett
    case FOB = 'FOB';  // Free On Board - Hajón átadva
    case CFR = 'CFR';  // Cost and Freight - Költség és fuvardíj
    case CIF = 'CIF';  // Cost, Insurance and Freight - Költség, biztosítás és fuvardíj
    case CPT = 'CPT';  // Carriage Paid To - Fuvarozás fizetve
    case CIP = 'CIP';  // Carriage and Insurance Paid To - Fuvarozás és biztosítás fizetve
    case DAP = 'DAP';  // Delivered at Place - Megnevezett helyen leszállítva
    case DPU = 'DPU';  // Delivered at Place Unloaded - Megnevezett helyen kirakodva
    case DDP = 'DDP';  // Delivered Duty Paid - Vámfizetéssel leszállítva

    public function getLabel(): string
    {
        return match ($this) {
            self::EXW => 'EXW - '.__('Ex Works'),
            self::FCA => 'FCA - '.__('Free Carrier'),
            self::FAS => 'FAS - '.__('Free Alongside Ship'),
            self::FOB => 'FOB - '.__('Free On Board'),
            self::CFR => 'CFR - '.__('Cost and Freight'),
            self::CIF => 'CIF - '.__('Cost, Insurance and Freight'),
            self::CPT => 'CPT - '.__('Carriage Paid To'),
            self::CIP => 'CIP - '.__('Carriage and Insurance Paid To'),
            self::DAP => 'DAP - '.__('Delivered at Place'),
            self::DPU => 'DPU - '.__('Delivered at Place Unloaded'),
            self::DDP => 'DDP - '.__('Delivered Duty Paid'),
        };
    }
}
