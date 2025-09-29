<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order_number'),
                TextEntry::make('type'),
                TextEntry::make('customer.id')
                    ->label('Customer')
                    ->placeholder('-'),
                TextEntry::make('supplier.id')
                    ->label('Supplier')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('order_date')
                    ->date(),
                TextEntry::make('delivery_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('total_amount')
                    ->numeric(),
                TextEntry::make('shipping_address')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Order $record): bool => $record->trashed()),
            ]);
    }
}
