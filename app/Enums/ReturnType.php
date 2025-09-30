<?php

declare(strict_types=1);

namespace App\Enums;

enum ReturnType: string
{
    case CUSTOMER_RETURN = 'CUSTOMER_RETURN';
    case SUPPLIER_RETURN = 'SUPPLIER_RETURN';

    public function label(): string
    {
        return match ($this) {
            self::CUSTOMER_RETURN => 'Customer Return',
            self::SUPPLIER_RETURN => 'Supplier Return',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CUSTOMER_RETURN => 'warning',
            self::SUPPLIER_RETURN => 'info',
        };
    }
}
