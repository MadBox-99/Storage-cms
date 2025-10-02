<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatDeclarations\Tables;

use App\Enums\IntrastatDirection;
use App\Enums\IntrastatStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

use function mb_str_pad;

final class IntrastatDeclarationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('declaration_number')
                    ->label('Bevallási szám')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('direction')
                    ->label('Irány')
                    ->badge()
                    ->sortable(),

                TextColumn::make('reference_period')
                    ->label('Hivatkozási időszak')
                    ->getStateUsing(fn ($record) => $record->reference_year.'/'.mb_str_pad((string) $record->reference_month, 2, '0', STR_PAD_LEFT))
                    ->sortable(['reference_year', 'reference_month']),

                TextColumn::make('status')
                    ->label('Státusz')
                    ->badge()
                    ->sortable(),

                TextColumn::make('declaration_date')
                    ->label('Bevallás dátuma')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('submitted_at')
                    ->label('Beadva')
                    ->date('Y-m-d')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('total_invoice_value')
                    ->label('Összes érték')
                    ->money('HUF')
                    ->sortable(),

                TextColumn::make('total_net_mass')
                    ->label('Összes tömeg')
                    ->numeric(decimalPlaces: 3)
                    ->suffix(' kg')
                    ->sortable(),

                TextColumn::make('intrastat_lines_count')
                    ->label('Sorok száma')
                    ->counts('intrastatLines')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('direction')
                    ->label('Irány')
                    ->options(IntrastatDirection::class),

                SelectFilter::make('status')
                    ->label('Státusz')
                    ->options(IntrastatStatus::class),

                SelectFilter::make('reference_year')
                    ->label('Év')
                    ->options(fn () => collect(range(now()->year - 2, now()->year + 1))->mapWithKeys(fn ($year) => [$year => $year])),
            ])
            ->defaultSort('declaration_date', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
