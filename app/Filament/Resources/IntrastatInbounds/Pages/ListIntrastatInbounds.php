<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatInbounds\Pages;

use App\Filament\Resources\IntrastatInbounds\IntrastatInboundResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListIntrastatInbounds extends ListRecords
{
    protected static string $resource = IntrastatInboundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
