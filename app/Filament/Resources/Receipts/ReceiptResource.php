<?php

declare(strict_types=1);

namespace App\Filament\Resources\Receipts;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Receipts\Pages\CreateReceipt;
use App\Filament\Resources\Receipts\Pages\EditReceipt;
use App\Filament\Resources\Receipts\Pages\ListReceipts;
use App\Filament\Resources\Receipts\Pages\ViewReceipt;
use App\Filament\Resources\Receipts\Schemas\ReceiptForm;
use App\Filament\Resources\Receipts\Schemas\ReceiptInfolist;
use App\Filament\Resources\Receipts\Tables\ReceiptsTable;
use App\Models\Receipt;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

final class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::SALES;

    public static function form(Schema $schema): Schema
    {
        return ReceiptForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReceiptInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReceiptsTable::configure($table);
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
            'index' => ListReceipts::route('/'),
            'create' => CreateReceipt::route('/create'),
            'view' => ViewReceipt::route('/{record}'),
            'edit' => EditReceipt::route('/{record}/edit'),
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
