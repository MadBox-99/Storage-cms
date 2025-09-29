<?php

namespace App\Filament\Resources\Warehouses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('capacity')
                    ->numeric(),
                Select::make('manager_id')
                    ->relationship('manager', 'id'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
