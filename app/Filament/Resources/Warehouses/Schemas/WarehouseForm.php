<?php

declare(strict_types=1);

namespace App\Filament\Resources\Warehouses\Schemas;

use App\Enums\WarehouseType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class WarehouseForm
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
                Select::make('type')
                    ->enum(WarehouseType::class)
                    ->required(),
                TextInput::make('capacity')
                    ->numeric(),
                Select::make('manager_id')
                    ->relationship('manager', 'full_name'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
