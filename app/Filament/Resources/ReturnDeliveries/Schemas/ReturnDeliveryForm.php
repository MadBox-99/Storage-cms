<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReturnDeliveries\Schemas;

use App\Enums\ProductCondition;
use App\Enums\ReturnReason;
use App\Enums\ReturnStatus;
use App\Enums\ReturnType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

final class ReturnDeliveryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Return Information')
                    ->schema([
                        TextInput::make('return_number')
                            ->label('Return Number')
                            ->default(fn () => 'RET-'.mb_strtoupper(uniqid()))
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),

                        Select::make('type')
                            ->label('Return Type')
                            ->options(ReturnType::class)
                            ->enum(ReturnType::class)
                            ->required()
                            ->live()
                            ->default(ReturnType::CUSTOMER_RETURN),

                        Select::make('warehouse_id')
                            ->relationship('warehouse', 'name')
                            ->label('Warehouse')
                            ->searchable()
                            ->preload()
                            ->required(),

                        DatePicker::make('return_date')
                            ->label('Return Date')
                            ->default(now())
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Related Records')
                    ->schema([
                        Select::make('order_id')
                            ->relationship('order', 'order_number')
                            ->label('Related Order')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('type') === ReturnType::CUSTOMER_RETURN),

                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->label('Customer')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('type') === ReturnType::CUSTOMER_RETURN),

                        Select::make('supplier_id')
                            ->relationship('supplier', 'company_name')
                            ->label('Supplier')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('type') === ReturnType::SUPPLIER_RETURN),

                        Select::make('processed_by')
                            ->relationship('processedBy', 'first_name')
                            ->label('Processed By')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Return Details')
                    ->schema([
                        Select::make('status')
                            ->options(ReturnStatus::class)
                            ->default(ReturnStatus::DRAFT)
                            ->required(),

                        Select::make('reason')
                            ->options(ReturnReason::class)
                            ->required(),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Return Items')
                    ->schema([
                        Repeater::make('returnLines')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(3),

                                TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->columnSpan(1),

                                TextInput::make('unit_price')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->prefix('HUF')
                                    ->columnSpan(2),

                                Select::make('condition')
                                    ->options(ProductCondition::class)
                                    ->required()
                                    ->default(ProductCondition::GOOD)
                                    ->columnSpan(2),

                                Select::make('return_reason')
                                    ->options(ReturnReason::class)
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('batch_number')
                                    ->label('Batch Number')
                                    ->columnSpan(2),

                                Textarea::make('note')
                                    ->label('Note')
                                    ->columnSpan(4),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->reorderable(false)
                            ->collapsible(),
                    ]),

                Section::make('Summary')
                    ->schema([
                        Placeholder::make('total_amount')
                            ->label('Total Amount')
                            ->content(fn ($record) => $record ? number_format($record->total_amount, 2).' HUF' : '0.00 HUF'),
                    ])
                    ->visibleOn('edit'),
            ]);
    }
}
