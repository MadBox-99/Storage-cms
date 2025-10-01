<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatOutbounds\Pages;

use App\Filament\Resources\IntrastatOutbounds\IntrastatOutboundResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateIntrastatOutbound extends CreateRecord
{
    protected static string $resource = IntrastatOutboundResource::class;
}
