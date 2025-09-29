<?php

namespace App\Filament\Resources\Receipts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ReceiptInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('receipt_number'),
                TextEntry::make('order.id')
                    ->label('Order')
                    ->placeholder('-'),
                TextEntry::make('warehouse.name')
                    ->label('Warehouse'),
                TextEntry::make('received_by')
                    ->numeric(),
                TextEntry::make('receipt_date')
                    ->date(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('total_amount')
                    ->numeric(),
                TextEntry::make('notes')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
