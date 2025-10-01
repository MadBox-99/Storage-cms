<?php

declare(strict_types=1);

namespace App\Filament\Resources\Invoices\Tables;

use App\Enums\InvoiceStatus;
use App\Enums\OrderType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice Number')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('supplier.company_name')
                    ->label('Supplier')
                    ->searchable()
                    ->visible(fn ($record) => $record?->type === OrderType::PURCHASE),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->visible(fn ($record) => $record?->type === OrderType::SALE),

                TextColumn::make('invoice_date')
                    ->label('Invoice Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->due_date < now() && $record->status !== InvoiceStatus::PAID ? 'danger' : null),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('HUF')
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('HUF')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(OrderType::class),

                SelectFilter::make('status')
                    ->options(InvoiceStatus::class),
            ])
            ->defaultSort('invoice_date', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
