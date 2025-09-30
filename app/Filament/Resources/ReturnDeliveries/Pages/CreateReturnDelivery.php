<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReturnDeliveries\Pages;

use App\Filament\Resources\ReturnDeliveries\ReturnDeliveryResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateReturnDelivery extends CreateRecord
{
    protected static string $resource = ReturnDeliveryResource::class;
}
