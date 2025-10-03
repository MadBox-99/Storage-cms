<?php

declare(strict_types=1);

namespace App\Filament\Resources\CnCodes;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CnCodes\Pages\CreateCnCode;
use App\Filament\Resources\CnCodes\Pages\EditCnCode;
use App\Filament\Resources\CnCodes\Pages\ListCnCodes;
use App\Filament\Resources\CnCodes\Schemas\CnCodeForm;
use App\Filament\Resources\CnCodes\Tables\CnCodesTable;
use App\Models\CnCode;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class CnCodeResource extends Resource
{
    protected static ?string $model = CnCode::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::INTRASTAT;

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return 'CN Kódok';
    }

    public static function getModelLabel(): string
    {
        return 'CN Kód';
    }

    public static function getPluralModelLabel(): string
    {
        return 'CN Kódok';
    }

    public static function form(Schema $schema): Schema
    {
        return CnCodeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CnCodesTable::configure($table);
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
            'index' => ListCnCodes::route('/'),
            'create' => CreateCnCode::route('/create'),
            'edit' => EditCnCode::route('/{record}/edit'),
        ];
    }
}
