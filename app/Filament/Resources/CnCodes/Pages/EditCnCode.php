<?php

declare(strict_types=1);

namespace App\Filament\Resources\CnCodes\Pages;

use App\Filament\Resources\CnCodes\CnCodeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditCnCode extends EditRecord
{
    protected static string $resource = CnCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
