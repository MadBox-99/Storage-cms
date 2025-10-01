<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatDeclarations\Pages;

use App\Filament\Resources\IntrastatDeclarations\IntrastatDeclarationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditIntrastatDeclaration extends EditRecord
{
    protected static string $resource = IntrastatDeclarationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
