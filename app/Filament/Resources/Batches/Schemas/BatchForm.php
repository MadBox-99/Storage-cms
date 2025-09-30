<?php

declare(strict_types=1);

namespace App\Filament\Resources\Batches\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class BatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('batch_number')
                            ->label('Batch Number / Sarzs Szám')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100),
                        Select::make('product_id')
                            ->label('Product / Termék')
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('supplier_id')
                            ->label('Supplier / Beszállító')
                            ->relationship('supplier', 'company_name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Section::make('Dates')
                    ->schema([
                        DatePicker::make('manufacture_date')
                            ->label('Manufacture Date / Gyártási Dátum'),
                        DatePicker::make('expiry_date')
                            ->label('Expiry Date / Lejárati Dátum'),
                    ])
                    ->columns(2),

                Section::make('Quantity & Status')
                    ->schema([
                        TextInput::make('quantity')
                            ->label('Quantity / Mennyiség')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Select::make('quality_status')
                            ->label('Quality Status / Minőségi Státusz')
                            ->options([
                                'PENDING_CHECK' => 'Pending Check',
                                'APPROVED' => 'Approved',
                                'REJECTED' => 'Rejected',
                                'QUARANTINE' => 'Quarantine',
                            ])
                            ->default('PENDING_CHECK')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Serial Numbers / Gyártási Számok')
                    ->schema([
                        Repeater::make('serial_numbers')
                            ->label('Serial Numbers')
                            ->schema([
                                TextInput::make('serial')
                                    ->label('Serial Number')
                                    ->required(),
                            ])
                            ->columns(1)
                            ->collapsible()
                            ->defaultItems(0)
                            ->addActionLabel('Add Serial Number'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
