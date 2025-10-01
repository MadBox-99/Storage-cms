<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatDeclarations;

use App\Enums\NavigationGroup;
use App\Filament\Resources\IntrastatDeclarations\Pages\CreateIntrastatDeclaration;
use App\Filament\Resources\IntrastatDeclarations\Pages\EditIntrastatDeclaration;
use App\Filament\Resources\IntrastatDeclarations\Pages\ListIntrastatDeclarations;
use App\Filament\Resources\IntrastatDeclarations\Schemas\IntrastatDeclarationForm;
use App\Filament\Resources\IntrastatDeclarations\Tables\IntrastatDeclarationsTable;
use App\Models\IntrastatDeclaration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class IntrastatDeclarationResource extends Resource
{
    protected static ?string $model = IntrastatDeclaration::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::ADMINISTRATION;

    public static function form(Schema $schema): Schema
    {
        return IntrastatDeclarationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IntrastatDeclarationsTable::configure($table);
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
            'index' => ListIntrastatDeclarations::route('/'),
            'create' => CreateIntrastatDeclaration::route('/create'),
            'edit' => EditIntrastatDeclaration::route('/{record}/edit'),
        ];
    }
}
