<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatDeclarations\Pages;

use App\Filament\Resources\IntrastatDeclarations\IntrastatDeclarationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListIntrastatDeclarations extends ListRecords
{
    protected static string $resource = IntrastatDeclarationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
