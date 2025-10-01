<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventories\Schemas;

use App\Enums\InventoryStatus;
use App\Enums\InventoryType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class InventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('inventory_number')
                    ->required(),
                Select::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->required(),
                TextInput::make('conducted_by')
                    ->required()
                    ->numeric(),
                DatePicker::make('inventory_date')
                    ->required(),
                Select::make('status')
                    ->options(InventoryStatus::class)
                    ->default('IN_PROGRESS')
                    ->required(),
                Select::make('type')
                    ->options(InventoryType::class)
                    ->required(),
                TextInput::make('variance_value')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('notes'),
            ]);
    }
}
