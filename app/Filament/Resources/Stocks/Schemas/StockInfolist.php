<?php

declare(strict_types=1);

namespace App\Filament\Resources\Stocks\Schemas;

use App\Models\Stock;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class StockInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('product.name')
                    ->label('Product'),
                TextEntry::make('warehouse.name')
                    ->label('Warehouse'),
                TextEntry::make('quantity')
                    ->numeric(),
                TextEntry::make('reserved_quantity')
                    ->numeric(),
                TextEntry::make('minimum_stock')
                    ->numeric(),
                TextEntry::make('maximum_stock')
                    ->numeric(),
                TextEntry::make('batch.id')
                    ->label('Batch')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('unit_cost')
                    ->numeric(),
                TextEntry::make('total_value')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Stock $record): bool => $record->trashed()),
            ]);
    }
}
