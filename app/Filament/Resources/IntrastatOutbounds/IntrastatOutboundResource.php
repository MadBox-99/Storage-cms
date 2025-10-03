<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatOutbounds;

use App\Enums\IntrastatDirection;
use App\Enums\NavigationGroup;
use App\Filament\Resources\IntrastatOutbounds\Actions\ExportIFormXmlAction;
use App\Filament\Resources\IntrastatOutbounds\Actions\ExportXmlAction;
use App\Filament\Resources\IntrastatOutbounds\Pages\ListIntrastatOutbounds;
use App\Filament\Resources\IntrastatOutbounds\Tables\IntrastatOutboundsTable;
use App\Models\IntrastatDeclaration;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class IntrastatOutboundResource extends Resource
{
    protected static ?string $model = IntrastatDeclaration::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::INTRASTAT;

    protected static ?string $navigationLabel = 'Kiszállítás (Dispatch)';

    protected static ?string $modelLabel = 'Kiszállítás';

    protected static ?string $pluralModelLabel = 'Kiszállítások';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('direction', IntrastatDirection::DISPATCH);
    }

    public static function table(Table $table): Table
    {
        return IntrastatOutboundsTable::configure($table)
            ->headerActions([
                ExportIFormXmlAction::make(),
                ExportXmlAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIntrastatOutbounds::route('/'),
        ];
    }
}
