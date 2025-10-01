<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatOutbounds\Pages;

use App\Filament\Resources\IntrastatOutbounds\IntrastatOutboundResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditIntrastatOutbound extends EditRecord
{
    protected static string $resource = IntrastatOutboundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
