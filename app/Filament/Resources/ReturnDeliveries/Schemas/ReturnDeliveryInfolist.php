<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReturnDeliveries\Schemas;

use App\Models\ReturnDelivery;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ReturnDeliveryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Return Information')
                    ->schema([
                        TextEntry::make('return_number')
                            ->label('Return Number'),

                        TextEntry::make('type')
                            ->label('Return Type')
                            ->badge(),

                        TextEntry::make('status')
                            ->badge(),

                        TextEntry::make('reason')
                            ->badge(),

                        TextEntry::make('warehouse.name')
                            ->label('Warehouse'),

                        TextEntry::make('return_date')
                            ->date(),

                        TextEntry::make('processedBy.first_name')
                            ->label('Processed By')
                            ->formatStateUsing(fn ($record) => $record->processedBy?->getFullName() ?? '-'),
                    ])
                    ->columns(3),

                Section::make('Related Records')
                    ->schema([
                        TextEntry::make('order.order_number')
                            ->label('Related Order')
                            ->placeholder('-')
                            ->visible(fn ($record) => $record->isCustomerReturn()),

                        TextEntry::make('order.customer.name')
                            ->label('Customer')
                            ->placeholder('-')
                            ->visible(fn ($record) => $record->isCustomerReturn()),

                        TextEntry::make('order.supplier.company_name')
                            ->label('Supplier')
                            ->placeholder('-')
                            ->visible(fn ($record) => $record->isSupplierReturn()),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->order_id || $record->customer_id || $record->supplier_id),

                Section::make('Return Items')
                    ->schema([
                        RepeatableEntry::make('returnDeliveryLines')
                            ->label('')
                            ->schema([
                                TextEntry::make('product.name')
                                    ->label('Product'),

                                TextEntry::make('quantity')
                                    ->label('Quantity')
                                    ->numeric(),

                                TextEntry::make('unit_price')
                                    ->label('Unit Price')
                                    ->money('HUF'),

                                TextEntry::make('condition')
                                    ->badge(),

                                TextEntry::make('return_reason')
                                    ->label('Reason')
                                    ->badge(),

                                TextEntry::make('batch_number')
                                    ->label('Batch')
                                    ->placeholder('-'),

                                TextEntry::make('note')
                                    ->label('Note')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ])
                            ->columns(3),
                    ]),

                Section::make('Additional Information')
                    ->schema([
                        TextEntry::make('total_amount')
                            ->label('Total Amount')
                            ->money('HUF'),

                        TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('created_at')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->dateTime(),

                        TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn (ReturnDelivery $record): bool => $record->trashed()),
                    ])
                    ->columns(2),
            ]);
    }
}
