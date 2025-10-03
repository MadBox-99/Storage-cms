<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatInbounds;

use App\Enums\IntrastatDirection;
use App\Enums\NavigationGroup;
use App\Filament\Resources\IntrastatInbounds\Actions\ExportIFormXmlAction;
use App\Filament\Resources\IntrastatInbounds\Actions\ExportXmlAction;
use App\Filament\Resources\IntrastatInbounds\Pages\ListIntrastatInbounds;
use App\Filament\Resources\IntrastatInbounds\Tables\IntrastatInboundsTable;
use App\Models\IntrastatDeclaration;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class IntrastatInboundResource extends Resource
{
    protected static ?string $model = IntrastatDeclaration::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::INTRASTAT;

    protected static ?string $navigationLabel = 'Beszállítás (Arrival)';

    protected static ?string $modelLabel = 'Beszállítás';

    protected static ?string $pluralModelLabel = 'Beszállítások';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('direction', IntrastatDirection::ARRIVAL);
    }

    public static function table(Table $table): Table
    {
        return IntrastatInboundsTable::configure($table)
            ->headerActions([
                ExportIFormXmlAction::make(),
                ExportXmlAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIntrastatInbounds::route('/'),
        ];
    }
}
