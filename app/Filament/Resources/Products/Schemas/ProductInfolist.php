<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatus;
use App\Models\Product;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextEntry::make('sku')
                            ->label('SKU / Cikkszám'),
                        TextEntry::make('name')
                            ->label('Product Name'),
                        TextEntry::make('barcode')
                            ->label('Barcode')
                            ->placeholder('-'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Classification')
                    ->schema([
                        TextEntry::make('category.name')
                            ->label('Category'),
                        TextEntry::make('supplier.company_name')
                            ->label('Primary Supplier'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (ProductStatus $state): string => match ($state) {
                                ProductStatus::ACTIVE => 'success',
                                ProductStatus::INACTIVE => 'gray',
                                ProductStatus::DISCONTINUED => 'danger',
                                ProductStatus::OUT_OF_STOCK => 'warning',
                                default => 'gray',
                            }),
                    ])
                    ->columns(3),

                Section::make('Measurements')
                    ->schema([
                        TextEntry::make('unit_of_measure')
                            ->label('Unit of Measure')
                            ->formatStateUsing(fn ($state) => $state?->label().' ('.$state?->abbreviation().')'),
                        TextEntry::make('weight')
                            ->label('Weight')
                            ->numeric()
                            ->suffix(' kg')
                            ->placeholder('-'),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('dimensions.length')
                                    ->label('Length')
                                    ->suffix(' cm')
                                    ->placeholder('-'),
                                TextEntry::make('dimensions.width')
                                    ->label('Width')
                                    ->suffix(' cm')
                                    ->placeholder('-'),
                                TextEntry::make('dimensions.height')
                                    ->label('Height')
                                    ->suffix(' cm')
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Pricing')
                    ->schema([
                        TextEntry::make('price')
                            ->label('Price')
                            ->money('HUF')
                            ->suffix(' / unit'),
                    ])
                    ->columns(1),

                Section::make('Stock Management')
                    ->schema([
                        TextEntry::make('min_stock')
                            ->label('Minimum Stock')
                            ->numeric(),
                        TextEntry::make('reorder_point')
                            ->label('Reorder Point')
                            ->numeric(),
                        TextEntry::make('max_stock')
                            ->label('Maximum Stock')
                            ->numeric(),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make('Stock by Warehouse')
                    ->schema([
                        RepeatableEntry::make('stocks')
                            ->label('')
                            ->schema([
                                TextEntry::make('warehouse.name')
                                    ->label('Warehouse'),
                                TextEntry::make('quantity')
                                    ->label('Available Quantity')
                                    ->numeric()
                                    ->badge()
                                    ->color(fn ($state, $record): string => match (true) {
                                        $state === 0 => 'gray',
                                        $record->isLowStock() => 'danger',
                                        default => 'success',
                                    }),
                                TextEntry::make('reserved_quantity')
                                    ->label('Reserved')
                                    ->numeric()
                                    ->badge()
                                    ->color('warning'),
                                TextEntry::make('Available')
                                    ->label('Available')
                                    ->state(fn ($record): int => $record->getAvailableQuantity())
                                    ->numeric()
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge(),
                            ])
                            ->columns(5)
                            ->columnSpanFull()
                            ->contained(false),
                        TextEntry::make('Total Stock')
                            ->label('Total Stock Across All Warehouses')
                            ->state(fn (Product $record): int => $record->getTotalStock())
                            ->numeric()
                            ->size('lg')
                            ->weight('bold')
                            ->badge()
                            ->color('primary'),
                    ])
                    ->collapsible(),

                Section::make('Expected Arrivals')
                    ->schema([
                        RepeatableEntry::make('expected_arrivals')
                            ->label('')
                            ->state(fn (Product $record) => $record->getExpectedArrivals())
                            ->schema([
                                TextEntry::make('order_number')
                                    ->label('Order #'),
                                TextEntry::make('supplier.name')
                                    ->label('Supplier'),
                                TextEntry::make('delivery_date')
                                    ->label('Expected Date')
                                    ->date()
                                    ->badge()
                                    ->color(function ($record): string {
                                        $daysUntil = now()->diffInDays($record->delivery_date, false);

                                        return match (true) {
                                            $daysUntil < 0 => 'danger',
                                            $daysUntil <= 3 => 'warning',
                                            default => 'success',
                                        };
                                    }),
                                TextEntry::make('quantity')
                                    ->label('Quantity')
                                    ->state(function ($record, Product $rootRecord): int {
                                        return $record->orderLines
                                            ->where('product_id', $rootRecord->id)
                                            ->sum('quantity');
                                    })
                                    ->numeric()
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('status')
                                    ->badge(),
                            ])
                            ->columns(5)
                            ->columnSpanFull()
                            ->contained(false)
                            ->visible(fn (Product $record): bool => $record->getExpectedArrivals()->isNotEmpty()),
                        TextEntry::make('Total Expected')
                            ->label('Total Expected Quantity')
                            ->state(fn (Product $record): int => $record->getTotalExpectedQuantity())
                            ->numeric()
                            ->size('lg')
                            ->weight('bold')
                            ->badge()
                            ->color('warning')
                            ->visible(fn (Product $record): bool => $record->getExpectedArrivals()->isNotEmpty()),
                    ])
                    ->collapsible()
                    ->visible(fn (Product $record): bool => $record->getExpectedArrivals()->isNotEmpty()),

                Section::make('Timestamps')
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn (Product $record): bool => $record->trashed()),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
