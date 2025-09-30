<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

final class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('barcode')
                    ->searchable(),
                TextColumn::make('unit_of_measure')
                    ->searchable(),
                TextColumn::make('weight')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->searchable(),
                TextColumn::make('supplier.company_name')
                    ->searchable(),
                TextColumn::make('min_stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reorder_point')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price')
                    ->money()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),

                IconColumn::make('stock_alert')
                    ->label('Stock Alert')
                    ->icon(fn (Product $record): string => match (true) {
                        $record->needsReorder() => 'heroicon-o-exclamation-triangle',
                        $record->getTotalStock() === 0 => 'heroicon-o-x-circle',
                        default => 'heroicon-o-check-circle',
                    })
                    ->color(fn (Product $record): string => match (true) {
                        $record->getTotalStock() === 0 => 'danger',
                        $record->needsReorder() => 'warning',
                        default => 'success',
                    })
                    ->tooltip(fn (Product $record): string => match (true) {
                        $record->getTotalStock() === 0 => 'Out of stock',
                        $record->needsReorder() => 'Reorder point reached - Current: '.$record->getTotalStock().', Reorder at: '.$record->reorder_point,
                        default => 'Stock level OK',
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->recordActionsPosition(RecordActionsPosition::BeforeCells)
            ->groups([
                'unit_of_measure',
                'category.name',
            ]);
    }
}
