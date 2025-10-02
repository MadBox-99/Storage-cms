<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ReturnType: string implements HasLabel
{
    case CUSTOMER_RETURN = 'customer_return';
    case SUPPLIER_RETURN = 'supplier_return';

    public function getLabel(): string
    {
        return match ($this) {
            self::CUSTOMER_RETURN => __('Customer Return'),
            self::SUPPLIER_RETURN => __('Supplier Return'),
        };
    }
}
