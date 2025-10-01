<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatInbounds\Pages;

use App\Filament\Resources\IntrastatInbounds\IntrastatInboundResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditIntrastatInbound extends EditRecord
{
    protected static string $resource = IntrastatInboundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
