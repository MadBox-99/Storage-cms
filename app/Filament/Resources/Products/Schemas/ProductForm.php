<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatus;
use App\Enums\UnitType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('sku')
                            ->label('SKU / Cikkszám')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100),
                        TextInput::make('name')
                            ->label('Product Name / Megnevezés')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('barcode')
                            ->label('Barcode / Vonalkód')
                            ->maxLength(100),
                        Textarea::make('description')
                            ->label('Description / Leírás')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Classification')
                    ->schema([
                        Select::make('category_id')
                            ->label('Category / Kategória')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required(),
                            ]),
                        Select::make('supplier_id')
                            ->label('Primary Supplier / Elsődleges Beszállító')
                            ->relationship('supplier', 'company_name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('status')
                            ->label('Status / Státusz')
                            ->options(ProductStatus::class)
                            ->default(ProductStatus::ACTIVE)
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Measurements')
                    ->schema([
                        Select::make('unit_of_measure')
                            ->label('Unit of Measure / Mértékegység')
                            ->options(UnitType::class)
                            ->default(UnitType::PIECE)
                            ->required(),
                        TextInput::make('weight')
                            ->label('Weight (kg) / Súly')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('kg'),
                        TextInput::make('dimensions.length')
                            ->label('Length (cm) / Hossz')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('cm'),
                        TextInput::make('dimensions.width')
                            ->label('Width (cm) / Szélesség')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('cm'),
                        TextInput::make('dimensions.height')
                            ->label('Height (cm) / Magasság')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('cm'),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make('Pricing')
                    ->schema([
                        TextInput::make('price')
                            ->label('Price / Ár')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('Ft')
                            ->suffix('/ unit'),
                    ])
                    ->columns(1),

                Section::make('Stock Management')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('min_stock')
                                    ->label('Minimum Stock / Min. Készlet')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->helperText('Alert when stock falls below this level'),
                                TextInput::make('reorder_point')
                                    ->label('Reorder Point / Rendelési pont')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->helperText('Trigger reorder when reaching this level'),
                                TextInput::make('max_stock')
                                    ->label('Maximum Stock / Max. Készlet')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->helperText('Maximum stock level to maintain'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
