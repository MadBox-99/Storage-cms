<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatDeclarations\Pages;

use App\Filament\Resources\IntrastatDeclarations\IntrastatDeclarationResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateIntrastatDeclaration extends CreateRecord
{
    protected static string $resource = IntrastatDeclarationResource::class;
}
