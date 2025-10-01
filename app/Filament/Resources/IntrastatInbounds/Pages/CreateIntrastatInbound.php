<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatInbounds\Pages;

use App\Filament\Resources\IntrastatInbounds\IntrastatInboundResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateIntrastatInbound extends CreateRecord
{
    protected static string $resource = IntrastatInboundResource::class;
}
