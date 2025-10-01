<?php

declare(strict_types=1);

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Filament\Schemas\Components\DatePicker;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Schema;

final class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment Information')
                    ->schema([
                        Group::make()
                            ->schema([
                                TextInput::make('payment_number')
                                    ->label('Payment Number')
                                    ->default(fn () => Payment::generatePaymentNumber())
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                Select::make('invoice_id')
                                    ->label('Invoice')
                                    ->relationship('invoice', 'invoice_number')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                DatePicker::make('payment_date')
                                    ->label('Payment Date')
                                    ->required()
                                    ->default(now()),
                            ])
                            ->columns(3),

                        Group::make()
                            ->schema([
                                TextInput::make('amount')
                                    ->label('Amount')
                                    ->numeric()
                                    ->required()
                                    ->prefix('HUF'),

                                Select::make('payment_method')
                                    ->label('Payment Method')
                                    ->options(PaymentMethod::class)
                                    ->required()
                                    ->default(PaymentMethod::BANK_TRANSFER),

                                Select::make('status')
                                    ->label('Status')
                                    ->options(PaymentStatus::class)
                                    ->required()
                                    ->default(PaymentStatus::PENDING),
                            ])
                            ->columns(3),

                        Group::make()
                            ->schema([
                                TextInput::make('transaction_id')
                                    ->label('Transaction ID')
                                    ->maxLength(255),

                                TextInput::make('currency')
                                    ->label('Currency')
                                    ->default('HUF')
                                    ->maxLength(3),

                                TextInput::make('exchange_rate')
                                    ->label('Exchange Rate')
                                    ->numeric()
                                    ->default(1.0),
                            ])
                            ->columns(3),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3),
                    ]),
            ]);
    }
}
