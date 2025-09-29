<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use App\Models\Supplier;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SupplierInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('code'),
                TextEntry::make('company_name'),
                TextEntry::make('trade_name')
                    ->placeholder('-'),
                TextEntry::make('tax_number')
                    ->placeholder('-'),
                TextEntry::make('eu_tax_number')
                    ->placeholder('-'),
                TextEntry::make('company_registration_number')
                    ->placeholder('-'),
                TextEntry::make('bank_account_number')
                    ->placeholder('-'),
                TextEntry::make('contact_person')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('website')
                    ->placeholder('-'),
                TextEntry::make('rating')
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Supplier $record): bool => $record->trashed()),
            ]);
    }
}
