<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatDeclarations\Schemas;

use App\Enums\IntrastatDirection;
use App\Enums\IntrastatStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class IntrastatDeclarationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bevallás adatai')
                    ->schema([
                        Group::make()
                            ->schema([
                                TextInput::make('declaration_number')
                                    ->label('Bevallási szám')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('INTRA-202510-ARR-001'),

                                Select::make('direction')
                                    ->label('Irány')
                                    ->options(IntrastatDirection::class)
                                    ->required()
                                    ->disabled(fn (?string $operation) => $operation === 'edit'),

                                Select::make('status')
                                    ->label('Státusz')
                                    ->options(IntrastatStatus::class)
                                    ->default(IntrastatStatus::DRAFT)
                                    ->required(),
                            ])
                            ->columns(3),

                        Group::make()
                            ->schema([
                                TextInput::make('reference_year')
                                    ->label('Hivatkozási év')
                                    ->numeric()
                                    ->required()
                                    ->minValue(2000)
                                    ->maxValue(2100)
                                    ->default(now()->year),

                                TextInput::make('reference_month')
                                    ->label('Hivatkozási hónap')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(12)
                                    ->default(now()->month),

                                DatePicker::make('declaration_date')
                                    ->label('Bevallás dátuma')
                                    ->required()
                                    ->default(now()),
                            ])
                            ->columns(3),

                        Textarea::make('notes')
                            ->label('Megjegyzések')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Beadási információk')
                    ->schema([
                        Group::make()
                            ->schema([
                                DatePicker::make('submitted_at')
                                    ->label('Beadás dátuma')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('submitted_by')
                                    ->label('Beadó')
                                    ->disabled()
                                    ->dehydrated(false),
                            ])
                            ->columns(2),
                    ])
                    ->visible(fn ($get) => $get('submitted_at') !== null),

                Section::make('Összesítő adatok')
                    ->description('Ezek az értékek automatikusan számolódnak az Intrastat sorokból')
                    ->schema([
                        Group::make()
                            ->schema([
                                TextInput::make('total_invoice_value')
                                    ->label('Összes számlázott érték (HUF)')
                                    ->numeric()
                                    ->readOnly()
                                    ->suffix('Ft')
                                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2, ',', ' ') : '0'),

                                TextInput::make('total_statistical_value')
                                    ->label('Összes statisztikai érték (HUF)')
                                    ->numeric()
                                    ->readOnly()
                                    ->suffix('Ft')
                                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2, ',', ' ') : '0'),

                                TextInput::make('total_net_mass')
                                    ->label('Összes nettó tömeg (kg)')
                                    ->numeric()
                                    ->readOnly()
                                    ->suffix('kg')
                                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 3, ',', ' ') : '0'),
                            ])
                            ->columns(3),
                    ])
                    ->collapsible()
                    ->visible(fn (?string $operation) => $operation === 'edit'),
            ]);
    }
}
