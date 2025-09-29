<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('barcode'),
                TextInput::make('unit_of_measure')
                    ->required(),
                TextInput::make('weight')
                    ->numeric(),
                TextInput::make('dimensions'),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                Select::make('supplier_id')
                    ->relationship('supplier', 'id')
                    ->required(),
                TextInput::make('min_stock')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('max_stock')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('reorder_point')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('status')
                    ->required()
                    ->default('ACTIVE'),
            ]);
    }
}
