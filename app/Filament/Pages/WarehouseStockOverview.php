<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\Product;
use App\Models\Warehouse;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

final class WarehouseStockOverview extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.warehouse-stock-overview';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::INVENTORY_MANAGEMENT;

    protected static ?string $title = 'Warehouse Stock Overview';

    protected static ?string $navigationLabel = 'Stock by Warehouse';

    protected static ?int $navigationSort = 10;

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->with(['stocks.warehouse']))
            ->columns($this->getColumns())
            ->filters([])
            ->defaultSort('name');
    }

    protected function getColumns(): array
    {
        $warehouses = Warehouse::query()->where('is_active', true)->get();

        $columns = [
            TextColumn::make('sku')
                ->label('SKU')
                ->sortable()
                ->searchable(),
            TextColumn::make('name')
                ->label('Product Name')
                ->sortable()
                ->searchable(),
        ];

        foreach ($warehouses as $warehouse) {
            $columns[] = TextColumn::make("stock_warehouse_{$warehouse->id}")
                ->label($warehouse->name)
                ->state(function (Product $record) use ($warehouse): string {
                    $stock = $record->stocks->firstWhere('warehouse_id', $warehouse->id);

                    return $stock ? (string) $stock->quantity : '0';
                })
                ->alignEnd()
                ->badge()
                ->color(function (Product $record) use ($warehouse): string {
                    $stock = $record->stocks->firstWhere('warehouse_id', $warehouse->id);

                    if (! $stock || $stock->quantity === 0) {
                        return 'gray';
                    }

                    if ($stock->isLowStock()) {
                        return 'danger';
                    }

                    return 'success';
                });
        }

        $columns[] = TextColumn::make('total_stock')
            ->label('Total Stock')
            ->state(fn (Product $record): int => $record->stocks->sum('quantity'))
            ->alignEnd()
            ->weight('bold')
            ->badge()
            ->color('primary');

        return $columns;
    }
}
