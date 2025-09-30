<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReturnDeliveries\Pages;

use App\Enums\ReturnStatus;
use App\Filament\Resources\ReturnDeliveries\ReturnDeliveryResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

final class ViewReturnDelivery extends ViewRecord
{
    protected static string $resource = ReturnDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn ($record) => $record->status->isEditable()),

            Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->status === ReturnStatus::INSPECTED)
                ->action(function ($record): void {
                    $record->approve();

                    Notification::make()
                        ->success()
                        ->title('Return approved')
                        ->send();
                }),

            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn ($record) => in_array($record->status, [ReturnStatus::PENDING_INSPECTION, ReturnStatus::INSPECTED]))
                ->action(function ($record): void {
                    $record->reject();

                    Notification::make()
                        ->success()
                        ->title('Return rejected')
                        ->send();
                }),

            Action::make('restock')
                ->label('Restock')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->status === ReturnStatus::APPROVED && $record->isCustomerReturn())
                ->action(function ($record): void {
                    $record->restock();

                    Notification::make()
                        ->success()
                        ->title('Items restocked')
                        ->body('Stock levels have been updated.')
                        ->send();
                }),
        ];
    }
}
