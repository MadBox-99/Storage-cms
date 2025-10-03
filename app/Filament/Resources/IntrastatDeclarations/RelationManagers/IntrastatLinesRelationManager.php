<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatDeclarations\RelationManagers;

use App\Enums\IntrastatDeliveryTerms;
use App\Enums\IntrastatTransactionType;
use App\Enums\IntrastatTransportMode;
use App\Models\CnCode;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class IntrastatLinesRelationManager extends RelationManager
{
    protected static string $relationship = 'intrastatLines';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Termék adatok')
                    ->schema([
                        Group::make()
                            ->schema([
                                Select::make('product_id')
                                    ->label('Termék')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state) {
                                            $product = Product::find($state);
                                            if ($product) {
                                                $set('cn_code', $product->cn_code);
                                                $set('country_of_origin', $product->country_of_origin);
                                            }
                                        }
                                    }),

                                Select::make('order_id')
                                    ->label('Rendelés')
                                    ->relationship('order', 'order_number')
                                    ->searchable()
                                    ->preload(),

                                Select::make('supplier_id')
                                    ->label('Beszállító')
                                    ->relationship('supplier', 'name')
                                    ->searchable()
                                    ->preload(),
                            ])
                            ->columns(3),

                        Group::make()
                            ->schema([
                                Select::make('cn_code_id')
                                    ->label('CN kód')
                                    ->relationship('cnCode', 'code')
                                    ->getOptionLabelFromRecordUsing(fn (CnCode $record) => "{$record->code} - {$record->description}")
                                    ->searchable(['code', 'description'])
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state) {
                                            $cnCode = CnCode::find($state);
                                            if ($cnCode) {
                                                $set('cn_code', $cnCode->code);
                                                $set('supplementary_unit', $cnCode->supplementary_unit);
                                                if ($cnCode->standard_mass_kg) {
                                                    $set('net_mass', $cnCode->standard_mass_kg);
                                                }
                                            }
                                        }
                                    })
                                    ->required(),

                                TextInput::make('quantity')
                                    ->label('Mennyiség')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0),

                                TextInput::make('net_mass')
                                    ->label('Nettó tömeg (kg)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->step(0.001),
                            ])
                            ->columns(3),
                    ]),

                Section::make('Értékadatok')
                    ->schema([
                        Group::make()
                            ->schema([
                                TextInput::make('invoice_value')
                                    ->label('Számlázott érték (HUF)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->prefix('Ft'),

                                TextInput::make('statistical_value')
                                    ->label('Statisztikai érték (HUF)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->prefix('Ft'),
                            ])
                            ->columns(2),
                    ]),

                Section::make('Országok')
                    ->schema([
                        Group::make()
                            ->schema([
                                TextInput::make('country_of_origin')
                                    ->label('Származási ország')
                                    ->maxLength(2)
                                    ->placeholder('DE'),

                                TextInput::make('country_of_consignment')
                                    ->label('Feladás országa')
                                    ->maxLength(2)
                                    ->placeholder('DE'),

                                TextInput::make('country_of_destination')
                                    ->label('Rendeltetési ország')
                                    ->maxLength(2)
                                    ->placeholder('HU'),
                            ])
                            ->columns(3),
                    ]),

                Section::make('Ügylet adatok')
                    ->schema([
                        Group::make()
                            ->schema([
                                Select::make('transaction_type')
                                    ->label('Ügylet jellege')
                                    ->options(IntrastatTransactionType::class)
                                    ->required(),

                                Select::make('transport_mode')
                                    ->label('Szállítási mód')
                                    ->options(IntrastatTransportMode::class)
                                    ->required(),

                                Select::make('delivery_terms')
                                    ->label('Szállítási feltétel')
                                    ->options(IntrastatDeliveryTerms::class)
                                    ->required(),
                            ])
                            ->columns(3),

                        Textarea::make('description')
                            ->label('Leírás')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Kiegészítő adatok')
                    ->schema([
                        Group::make()
                            ->schema([
                                TextInput::make('supplementary_unit')
                                    ->label('Kiegészítő mértékegység')
                                    ->maxLength(255),

                                TextInput::make('supplementary_quantity')
                                    ->label('Kiegészítő mennyiség')
                                    ->numeric()
                                    ->minValue(0),
                            ])
                            ->columns(2),
                    ])
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('cn_code')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Termék')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cnCode.code')
                    ->label('CN kód')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Model $record) => $record->cnCode?->description),

                TextColumn::make('quantity')
                    ->label('Mennyiség')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('net_mass')
                    ->label('Tömeg (kg)')
                    ->numeric(decimalPlaces: 3)
                    ->sortable(),

                TextColumn::make('invoice_value')
                    ->label('Érték')
                    ->money('HUF')
                    ->sortable(),

                TextColumn::make('country_of_origin')
                    ->label('Származás')
                    ->sortable(),

                TextColumn::make('transaction_type')
                    ->label('Ügylet')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
