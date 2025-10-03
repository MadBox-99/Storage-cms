<?php

declare(strict_types=1);

namespace App\Filament\Resources\CnCodes\Pages;

use App\Filament\Resources\CnCodes\CnCodeResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCnCode extends CreateRecord
{
    protected static string $resource = CnCodeResource::class;
}
