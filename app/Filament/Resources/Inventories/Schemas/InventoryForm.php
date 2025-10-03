<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventories\Schemas;

use App\Enums\InventoryStatus;
use App\Enums\InventoryType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
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
                Select::make('conducted_by')
                    ->required()
                    ->relationship('conductedBy', 'full_name'),
                DatePicker::make('inventory_date')
                    ->default(now())
                    ->required(),
                Select::make('status')
                    ->options(InventoryStatus::class)
                    ->enum(InventoryStatus::class)
                    ->default(InventoryStatus::IN_PROGRESS)
                    ->required(),
                Select::make('type')
                    ->options(InventoryType::class)
                    ->enum(InventoryType::class)
                    ->required(),

                RichEditor::make('notes')->columnSpanFull(),
            ]);
    }
}
