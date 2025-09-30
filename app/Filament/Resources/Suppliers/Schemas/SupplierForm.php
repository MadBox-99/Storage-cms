<?php

declare(strict_types=1);

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('code')
                            ->required(),
                        TextInput::make('company_name')
                            ->required(),
                        TextInput::make('trade_name'),
                    ])
                    ->columns(2),

                Section::make('Headquarters Address')
                    ->schema([
                        TextInput::make('headquarters.street')
                            ->label('Street'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('headquarters.city')
                                    ->label('City'),
                                TextInput::make('headquarters.state')
                                    ->label('State'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('headquarters.zip')
                                    ->label('Zip Code'),
                                TextInput::make('headquarters.country')
                                    ->label('Country'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Mailing Address')
                    ->schema([
                        TextInput::make('mailing_address.street')
                            ->label('Street'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('mailing_address.city')
                                    ->label('City'),
                                TextInput::make('mailing_address.state')
                                    ->label('State'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('mailing_address.zip')
                                    ->label('Zip Code'),
                                TextInput::make('mailing_address.country')
                                    ->label('Country'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Company Details')
                    ->schema([
                        TextInput::make('tax_number'),
                        TextInput::make('eu_tax_number'),
                        TextInput::make('company_registration_number'),
                        TextInput::make('bank_account_number'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Contact Information')
                    ->schema([
                        TextInput::make('contact_person'),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email(),
                        TextInput::make('phone')
                            ->tel(),
                        TextInput::make('website')
                            ->url(),
                    ])
                    ->columns(2),

                Section::make('Status')
                    ->schema([
                        TextInput::make('rating'),
                        Toggle::make('is_active')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
