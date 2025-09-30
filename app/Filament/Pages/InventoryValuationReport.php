<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Services\Inventory\InventoryValuationService;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class InventoryValuationReport extends Page implements HasTable
{
    use InteractsWithTable;

    public ?string $groupBy = 'warehouse';

    public ?int $warehouseFilter = null;

    public ?int $categoryFilter = null;

    protected string $view = 'filament.pages.inventory-valuation-report';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::INVENTORY_MANAGEMENT;

    protected static ?string $title = 'Inventory Valuation Report';

    protected static ?string $navigationLabel = 'Valuation Report';

    protected static ?int $navigationSort = 15;

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Select::make('groupBy')
                    ->label('Group By')
                    ->options([
                        'warehouse' => 'Warehouse',
                        'product' => 'Product',
                        'category' => 'Category',
                    ])
                    ->default('warehouse')
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),

                Select::make('warehouseFilter')
                    ->label('Filter by Warehouse')
                    ->options(Warehouse::query()->where('is_active', true)->pluck('name', 'id'))
                    ->placeholder('All Warehouses')
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable())
                    ->visible(fn () => $this->groupBy !== 'warehouse'),

                Select::make('categoryFilter')
                    ->label('Filter by Category')
                    ->options(Category::query()->pluck('name', 'id'))
                    ->placeholder('All Categories')
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable())
                    ->visible(fn () => in_array($this->groupBy, ['product', 'category'])),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->defaultSort('name')
            ->paginated([10, 25, 50, 100])
            ->heading($this->getTableHeading());
    }

    protected function getTableQuery(): Builder
    {
        return match ($this->groupBy) {
            'product' => $this->getProductQuery(),
            'category' => $this->getCategoryQuery(),
            default => $this->getWarehouseQuery(),
        };
    }

    protected function getWarehouseQuery(): Builder
    {
        return Warehouse::query()
            ->where('is_active', true)
            ->with(['stocks.product'])
            ->when($this->warehouseFilter, fn (Builder $q) => $q->where('id', $this->warehouseFilter));
    }

    protected function getProductQuery(): Builder
    {
        return Product::query()
            ->with(['stocks.warehouse', 'category'])
            ->when($this->warehouseFilter, function (Builder $q) {
                $q->whereHas('stocks', fn (Builder $sq) => $sq->where('warehouse_id', $this->warehouseFilter));
            })
            ->when($this->categoryFilter, fn (Builder $q) => $q->where('category_id', $this->categoryFilter));
    }

    protected function getCategoryQuery(): Builder
    {
        return Category::query()
            ->with(['products.stocks'])
            ->when($this->categoryFilter, fn (Builder $q) => $q->where('id', $this->categoryFilter));
    }

    protected function getTableColumns(): array
    {
        return match ($this->groupBy) {
            'product' => $this->getProductColumns(),
            'category' => $this->getCategoryColumns(),
            default => $this->getWarehouseColumns(),
        };
    }

    protected function getWarehouseColumns(): array
    {
        $service = app(InventoryValuationService::class);

        return [
            TextColumn::make('name')
                ->label('Warehouse')
                ->searchable()
                ->sortable(),

            TextColumn::make('code')
                ->label('Code')
                ->searchable(),

            TextColumn::make('valuation_method')
                ->label('Valuation Method')
                ->formatStateUsing(fn ($state) => $state?->getLabel()),

            TextColumn::make('total_quantity')
                ->label('Total Quantity')
                ->state(fn (Warehouse $record): int => $record->stocks->sum('quantity'))
                ->numeric()
                ->alignEnd(),

            TextColumn::make('total_value')
                ->label('Total Value')
                ->state(fn (Warehouse $record) => $service->getWarehouseTotalValue($record))
                ->money('HUF')
                ->alignEnd()
                ->weight('bold')
                ->color('success'),
        ];
    }

    protected function getProductColumns(): array
    {
        $service = app(InventoryValuationService::class);

        return [
            TextColumn::make('sku')
                ->label('SKU')
                ->searchable()
                ->sortable(),

            TextColumn::make('name')
                ->label('Product')
                ->searchable()
                ->sortable(),

            TextColumn::make('category.name')
                ->label('Category')
                ->searchable(),

            TextColumn::make('total_quantity')
                ->label('Total Quantity')
                ->state(fn (Product $record): int => $record->stocks->sum('quantity'))
                ->numeric()
                ->alignEnd(),

            TextColumn::make('warehouses')
                ->label('Warehouses')
                ->state(fn (Product $record): int => $record->stocks->count())
                ->numeric()
                ->alignEnd(),

            TextColumn::make('total_value')
                ->label('Total Value')
                ->state(fn (Product $record) => $service->getProductTotalValue($record))
                ->money('HUF')
                ->alignEnd()
                ->weight('bold')
                ->color('success'),
        ];
    }

    protected function getCategoryColumns(): array
    {
        $service = app(InventoryValuationService::class);

        return [
            TextColumn::make('name')
                ->label('Category')
                ->searchable()
                ->sortable(),

            TextColumn::make('code')
                ->label('Code')
                ->searchable(),

            TextColumn::make('product_count')
                ->label('Products Count')
                ->state(fn (Category $record): int => $record->products->count())
                ->numeric()
                ->alignEnd(),

            TextColumn::make('total_quantity')
                ->label('Total Quantity')
                ->state(function (Category $record): int|string {

                    return Stock::query()
                        ->whereHas('product',
                            fn (Builder $q) => $q->where('category_id', $record->id))
                        ->sum('quantity');
                })
                ->numeric()
                ->alignEnd(),

            TextColumn::make('total_value')
                ->label('Total Value')
                ->state(fn (Category $record) => $service->getCategoryTotalValue($record->id))
                ->money('HUF')
                ->alignEnd()
                ->weight('bold')
                ->color('success'),
        ];
    }

    protected function getTableHeading(): string
    {
        return match ($this->groupBy) {
            'warehouse' => 'Inventory Value by Warehouse',
            'product' => 'Inventory Value by Product',
            'category' => 'Inventory Value by Category',
            default => 'Inventory Valuation',
        };
    }
}
