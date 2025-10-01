<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->default(fn () => 'ORD-'.now()->format('Ymd').'-'.mb_strtoupper(mb_substr(bin2hex(random_bytes(3)), 0, 6)))
                    ->required(),
                Select::make('type')
                    ->label(__('Order Type'))
                    ->options(OrderType::class)
                    ->enum(OrderType::class)
                    ->required(),
                Select::make('customer_id')
                    ->relationship('customer', 'name'),
                Select::make('supplier_id')
                    ->relationship('supplier', 'company_name'),
                Select::make('status')
                    ->options(OrderStatus::class)
                    ->default(OrderStatus::DRAFT)
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
