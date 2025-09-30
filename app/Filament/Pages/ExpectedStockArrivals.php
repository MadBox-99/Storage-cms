<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class ExpectedStockArrivals extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.expected-stock-arrivals';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::INVENTORY_MANAGEMENT;

    protected static ?string $title = 'Expected Stock Arrivals';

    protected static ?string $navigationLabel = 'Expected Arrivals';

    protected static ?int $navigationSort = 11;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['orderLines.product', 'supplier'])
                    ->where('type', OrderType::PURCHASE)
                    ->whereIn('status', [OrderStatus::CONFIRMED, OrderStatus::PROCESSING, OrderStatus::SHIPPED])
                    ->whereNotNull('delivery_date')
                    ->orderBy('delivery_date', 'asc')
            )
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier.company_name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('delivery_date')
                    ->label('Expected Arrival')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(function (Order $record): string {
                        $daysUntil = now()->diffInDays($record->delivery_date, false);

                        return match (true) {
                            $daysUntil < 0 => 'danger',
                            $daysUntil <= 3 => 'warning',
                            default => 'success',
                        };
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('order_date')
                    ->label('Order Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_amount')
                    ->label('Total Value')
                    ->money('HUF')
                    ->sortable(),

                TextColumn::make('orderLines')
                    ->label('Items')
                    ->state(fn (Order $record): int => $record->orderLines->count())
                    ->badge()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderStatus::class),

                SelectFilter::make('supplier')
                    ->relationship('supplier', 'company_name'),
            ])
            ->defaultSort('delivery_date', 'asc')
            ->striped();
    }
}
