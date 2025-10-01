<?php

declare(strict_types=1);

namespace App\Filament\Resources\Customers\Schemas;

use App\Enums\CustomerType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Information')
                    ->schema([
                        TextInput::make('customer_code')
                            ->required(),
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required(),
                        TextInput::make('phone')
                            ->tel(),
                        Select::make('type')
                            ->options(CustomerType::class)
                            ->enum(CustomerType::class),
                    ])
                    ->columns(2),

                Section::make('Billing Address')
                    ->schema([
                        TextInput::make('billing_address.street')
                            ->label('Street'),
                        TextInput::make('billing_address.city')
                            ->label('City'),
                        TextInput::make('billing_address.state')
                            ->label('State'),
                        TextInput::make('billing_address.postal_code')
                            ->label('Postal Code'),
                        TextInput::make('billing_address.country')
                            ->label('Country'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Shipping Address')
                    ->schema([
                        TextInput::make('shipping_address.street')
                            ->label('Street'),
                        TextInput::make('shipping_address.city')
                            ->label('City'),
                        TextInput::make('shipping_address.state')
                            ->label('State'),
                        TextInput::make('shipping_address.postal_code')
                            ->label('Postal Code'),
                        TextInput::make('shipping_address.country')
                            ->label('Country'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Financial Information')
                    ->schema([
                        TextInput::make('credit_limit')
                            ->required()
                            ->numeric()
                            ->default(0.0)
                            ->prefix('HUF'),
                        TextInput::make('balance')
                            ->required()
                            ->numeric()
                            ->default(0.0)
                            ->prefix('HUF'),
                    ])
                    ->columns(2),
            ]);
    }
}
