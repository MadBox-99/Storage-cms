<?php

declare(strict_types=1);

namespace App\Filament\Resources\Invoices\Schemas;

use App\Enums\InvoiceStatus;
use App\Enums\OrderType;
use App\Models\Customer;
use App\Models\Supplier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice Information')
                    ->schema([
                        Group::make()
                            ->schema([
                                TextInput::make('invoice_number')
                                    ->label('Invoice Number')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                Select::make('type')
                                    ->label('Type')
                                    ->options(OrderType::class)
                                    ->required()
                                    ->live(),

                                Select::make('status')
                                    ->label('Status')
                                    ->options(InvoiceStatus::class)
                                    ->default(InvoiceStatus::DRAFT)
                                    ->required(),
                            ])
                            ->columns(3),

                        Group::make()
                            ->schema([
                                Select::make('receipt_id')
                                    ->label('Receipt')
                                    ->relationship('receipt', 'receipt_number')
                                    ->searchable()
                                    ->preload(),

                                Select::make('supplier_id')
                                    ->label('Supplier')
                                    ->options(Supplier::query()->pluck('company_name', 'id'))
                                    ->searchable()
                                    ->visible(fn ($get) => $get('type') === OrderType::PURCHASE),

                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->options(Customer::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->visible(fn ($get) => $get('type') === OrderType::SALE),
                            ])
                            ->columns(2),

                        Group::make()
                            ->schema([
                                DatePicker::make('invoice_date')
                                    ->label('Invoice Date')
                                    ->required()
                                    ->default(now()),

                                DatePicker::make('due_date')
                                    ->label('Due Date')
                                    ->required()
                                    ->default(now()->addDays(30)),

                                DatePicker::make('payment_date')
                                    ->label('Payment Date'),
                            ])
                            ->columns(3),
                    ]),

                Section::make('Invoice Lines')
                    ->schema([
                        Repeater::make('invoiceLines')
                            ->relationship()
                            ->schema([
                                TextInput::make('description')
                                    ->label('Description')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->live(onBlur: true),

                                TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->required()
                                    ->live(onBlur: true),

                                TextInput::make('tax_rate')
                                    ->label('Tax Rate (%)')
                                    ->numeric()
                                    ->default(27)
                                    ->required()
                                    ->live(onBlur: true),

                                Placeholder::make('line_total')
                                    ->label('Line Total')
                                    ->content(fn ($get) => number_format(
                                        ($get('quantity') ?? 0) * ($get('unit_price') ?? 0) * (1 + ($get('tax_rate') ?? 0) / 100),
                                        2
                                    ).' HUF'),
                            ])
                            ->columns(5)
                            ->defaultItems(1)
                            ->addActionLabel('Add Line')
                            ->reorderable(),
                    ]),

                Section::make('Totals')
                    ->schema([
                        Group::make()
                            ->schema([
                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->readOnly()
                                    ->prefix('HUF'),

                                TextInput::make('tax_amount')
                                    ->label('Tax Amount')
                                    ->numeric()
                                    ->readOnly()
                                    ->prefix('HUF'),

                                TextInput::make('total_amount')
                                    ->label('Total Amount')
                                    ->numeric()
                                    ->readOnly()
                                    ->prefix('HUF'),

                                TextInput::make('paid_amount')
                                    ->label('Paid Amount')
                                    ->numeric()
                                    ->readOnly()
                                    ->prefix('HUF'),
                            ])
                            ->columns(4),
                    ]),

                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3),
                    ])
                    ->collapsible(),
            ]);
    }
}
