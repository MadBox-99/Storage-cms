<?php

declare(strict_types=1);

namespace App\Filament\Resources\Suppliers\Schemas;

use App\Enums\SupplierRating;
use App\Models\Supplier;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class SupplierInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextEntry::make('code'),
                        TextEntry::make('company_name'),
                        TextEntry::make('trade_name')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Headquarters Address')
                    ->schema([
                        TextEntry::make('headquarters.street')
                            ->label('Street')
                            ->placeholder('-'),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('headquarters.city')
                                    ->label('City')
                                    ->placeholder('-'),
                                TextEntry::make('headquarters.state')
                                    ->label('State')
                                    ->placeholder('-'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('headquarters.zip')
                                    ->label('Zip Code')
                                    ->placeholder('-'),
                                TextEntry::make('headquarters.country')
                                    ->label('Country')
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Mailing Address')
                    ->schema([
                        TextEntry::make('mailing_address.street')
                            ->label('Street')
                            ->placeholder('-'),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('mailing_address.city')
                                    ->label('City')
                                    ->placeholder('-'),
                                TextEntry::make('mailing_address.state')
                                    ->label('State')
                                    ->placeholder('-'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('mailing_address.zip')
                                    ->label('Zip Code')
                                    ->placeholder('-'),
                                TextEntry::make('mailing_address.country')
                                    ->label('Country')
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Company Details')
                    ->schema([
                        TextEntry::make('tax_number')
                            ->placeholder('-'),
                        TextEntry::make('eu_tax_number')
                            ->placeholder('-'),
                        TextEntry::make('company_registration_number')
                            ->placeholder('-'),
                        TextEntry::make('bank_account_number')
                            ->placeholder('-'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Contact Information')
                    ->schema([
                        TextEntry::make('contact_person')
                            ->placeholder('-'),
                        TextEntry::make('email')
                            ->label('Email address')
                            ->placeholder('-'),
                        TextEntry::make('phone')
                            ->placeholder('-'),
                        TextEntry::make('website')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Status')
                    ->schema([
                        TextEntry::make('rating')
                            ->placeholder('-')
                            ->badge()
                            ->color(fn (SupplierRating $state): string => match ($state) {
                                SupplierRating::EXCELLENT => 'success',
                                SupplierRating::GOOD => 'info',
                                SupplierRating::AVERAGE => 'warning',
                                SupplierRating::POOR => 'danger',
                                SupplierRating::BLACKLISTED => 'danger',
                                default => 'gray',
                            }),
                        IconEntry::make('is_active')
                            ->boolean(),
                    ])
                    ->columns(2),

                Section::make('Timestamps')
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn (Supplier $record): bool => $record->trashed()),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
