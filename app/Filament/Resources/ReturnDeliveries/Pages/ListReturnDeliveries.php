<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReturnDeliveries\Pages;

use App\Filament\Resources\ReturnDeliveries\ReturnDeliveryResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListReturnDeliveries extends ListRecords
{
    protected static string $resource = ReturnDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('createWithSteps')
                ->label('Create with Wizard')
                ->icon('heroicon-o-sparkles')
                ->url(ReturnDeliveryResource::getUrl('create-with-steps')),
        ];
    }
}
