<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->required(),
                TextInput::make('type')
                    ->required(),
                Select::make('customer_id')
                    ->relationship('customer', 'id'),
                Select::make('supplier_id')
                    ->relationship('supplier', 'id'),
                Select::make('status')
                    ->options(OrderStatus::class)
                    ->default('DRAFT')
                    ->required(),
                DatePicker::make('order_date')
                    ->required(),
                DatePicker::make('delivery_date'),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('shipping_address')
                    ->columnSpanFull(),
            ]);
    }
}
