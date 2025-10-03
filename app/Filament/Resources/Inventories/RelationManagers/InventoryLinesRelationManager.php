<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventories\RelationManagers;

use App\Enums\DiscrepancyType;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class InventoryLinesRelationManager extends RelationManager
{
    protected static string $relationship = 'inventoryLines';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Termék adatok')
                    ->schema([
                        Select::make('product_id')
                            ->label('Termék')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $product = Product::query()
                                        ->with('stocks')
                                        ->find($state);

                                    if ($product && $this->getOwnerRecord()->warehouse_id) {
                                        $stock = $product->stocks()
                                            ->where('warehouse_id', $this->getOwnerRecord()->warehouse_id)
                                            ->first();

                                        if ($stock) {
                                            $set('system_quantity', $stock->quantity);
                                        }
                                    }
                                }
                            }),

                        Group::make()
                            ->schema([
                                TextInput::make('batch_number')
                                    ->label('Tétel szám')
                                    ->maxLength(255),

                                DatePicker::make('expiry_date')
                                    ->label('Lejárati dátum'),
                            ])
                            ->columns(2),
                    ]),

                Section::make('Mennyiségi adatok')
                    ->columnSpanFull()
                    ->schema([
                        Group::make()
                            ->schema([
                                TextInput::make('system_quantity')
                                    ->label('Rendszer mennyiség')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('actual_quantity')
                                    ->label('Tényleges mennyiség')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->live(debounce: 1000)
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $systemQuantity = $get('system_quantity') ?? 0;
                                        $actualQuantity = $get('actual_quantity') ?? 0;
                                        $unitCost = $state ?? 0;

                                        $variance = ($actualQuantity - $systemQuantity) * $unitCost;
                                        $set('variance_value', $variance);
                                    }),

                                TextInput::make('unit_cost')
                                    ->label('Egységár')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->prefix('Ft')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $systemQuantity = $get('system_quantity') ?? 0;
                                        $actualQuantity = $get('actual_quantity') ?? 0;
                                        $unitCost = $state ?? 0;

                                        $variance = ($actualQuantity - $systemQuantity) * $unitCost;
                                        $set('variance_value', $variance);
                                    }),
                            ])
                            ->columns(3),

                        TextInput::make('variance_value')
                            ->label('Eltérés értéke')
                            ->afterStateHydrated(function ($state, $get, $set) {
                                $systemQuantity = $get('system_quantity') ?? 0;
                                $actualQuantity = $get('actual_quantity') ?? 0;
                                $unitCost = $get('unit_cost') ?? 0;

                                $variance = ($actualQuantity - $systemQuantity) * $unitCost;
                                $set('variance_value', $variance);
                            })
                            ->numeric()
                            ->disabled()
                            ->prefix('Ft')
                            ->dehydrated(false),
                    ]),

                Section::make('Állapot és megjegyzés')
                    ->schema([
                        Select::make('condition')
                            ->options(DiscrepancyType::class)
                            ->enum(DiscrepancyType::class)
                            ->label('Állapot'),

                        Textarea::make('note')
                            ->label('Megjegyzés')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Termék')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('batch_number')
                    ->label('Tétel szám')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('system_quantity')
                    ->label('Rendszer')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('actual_quantity')
                    ->label('Tényleges')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('variance_quantity')
                    ->label('Eltérés')
                    ->getStateUsing(fn (Model $record) => $record->calculateVarianceQuantity())
                    ->numeric()
                    ->badge()
                    ->color(fn (Model $record) => match ($record->getDiscrepancyType()) {
                        DiscrepancyType::SHORTAGE => 'danger',
                        DiscrepancyType::OVERAGE => 'warning',
                        DiscrepancyType::MATCH => 'success',
                    })
                    ->sortable(),

                TextColumn::make('unit_cost')
                    ->label('Egységár')
                    ->money('HUF')
                    ->sortable(),

                TextColumn::make('variance_value')
                    ->label('Eltérés értéke')
                    ->getStateUsing(fn (Model $record) => $record->calculateVarianceValue())
                    ->money('HUF')
                    ->sortable()
                    ->color(fn (Model $record) => $record->hasVariance() ? 'danger' : 'success'),

                TextColumn::make('condition')
                    ->label('Állapot')
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('expiry_date')
                    ->label('Lejárat')
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['system_quantity'] = $data['system_quantity'] ?? 0;
                        $data['actual_quantity'] = $data['actual_quantity'] ?? 0;

                        return $data;
                    })
                    ->after(function () {
                        $this->getOwnerRecord()->calculateVariance();
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(function () {
                        $this->getOwnerRecord()->calculateVariance();
                    }),
                DeleteAction::make()
                    ->after(function () {
                        $this->getOwnerRecord()->calculateVariance();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(function () {
                            $this->getOwnerRecord()->calculateVariance();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
