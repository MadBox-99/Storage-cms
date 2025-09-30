<?php

declare(strict_types=1);

namespace App\Filament\Resources\Batches\Pages;

use App\Filament\Resources\Batches\BatchResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateBatch extends CreateRecord
{
    protected static string $resource = BatchResource::class;
}
