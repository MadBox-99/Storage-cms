<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatOutbounds\Pages;

use App\Filament\Resources\IntrastatOutbounds\IntrastatOutboundResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListIntrastatOutbounds extends ListRecords
{
    protected static string $resource = IntrastatOutboundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
