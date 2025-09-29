<?php

namespace App\Filament\Resources\Receipts\Schemas;

use App\Enums\ReceiptStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReceiptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('receipt_number')
                    ->required(),
                Select::make('order_id')
                    ->relationship('order', 'id'),
                Select::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->required(),
                TextInput::make('received_by')
                    ->required()
                    ->numeric(),
                DatePicker::make('receipt_date')
                    ->required(),
                Select::make('status')
                    ->options(ReceiptStatus::class)
                    ->default('PENDING')
                    ->required(),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('notes'),
            ]);
    }
}
