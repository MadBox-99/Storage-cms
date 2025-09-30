<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReturnDeliveries;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ReturnDeliveries\Pages\CreateReturnDelivery;
use App\Filament\Resources\ReturnDeliveries\Pages\EditReturnDelivery;
use App\Filament\Resources\ReturnDeliveries\Pages\ListReturnDeliveries;
use App\Filament\Resources\ReturnDeliveries\Pages\ViewReturnDelivery;
use App\Filament\Resources\ReturnDeliveries\Schemas\ReturnDeliveryForm;
use App\Filament\Resources\ReturnDeliveries\Schemas\ReturnDeliveryInfolist;
use App\Filament\Resources\ReturnDeliveries\Tables\ReturnDeliveriesTable;
use App\Models\ReturnDelivery;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

final class ReturnDeliveryResource extends Resource
{
    protected static ?string $model = ReturnDelivery::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::INVENTORY_MANAGEMENT;

    protected static ?string $navigationLabel = 'Returns';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return ReturnDeliveryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReturnDeliveryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReturnDeliveriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReturnDeliveries::route('/'),
            'create' => CreateReturnDelivery::route('/create'),
            'view' => ViewReturnDelivery::route('/{record}'),
            'edit' => EditReturnDelivery::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
