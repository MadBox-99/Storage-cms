<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('company_name')
                    ->required(),
                TextInput::make('trade_name'),
                TextInput::make('headquarters'),
                TextInput::make('mailing_address'),
                TextInput::make('tax_number'),
                TextInput::make('eu_tax_number'),
                TextInput::make('company_registration_number'),
                TextInput::make('bank_account_number'),
                TextInput::make('contact_person'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('website')
                    ->url(),
                TextInput::make('rating'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
