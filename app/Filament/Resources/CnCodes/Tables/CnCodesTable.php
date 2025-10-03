<?php

declare(strict_types=1);

namespace App\Filament\Resources\CnCodes\Tables;

use App\Filament\Resources\CnCodes\Exports\CnCodeExport;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class CnCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kód')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('description')
                    ->label('Megnevezés')
                    ->searchable()
                    ->limit(60)
                    ->tooltip(fn (TextColumn $column): ?string => $column->getState()),

                TextColumn::make('supplementary_unit')
                    ->label('Kiegészítő mértékegység')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('intrastat_lines_count')
                    ->label('Használat')
                    ->counts('intrastatLines')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label('Létrehozva')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Módosítva')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(CnCodeExport::class)
                        ->label('Kijelöltek exportálása'),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('code', 'asc');
    }
}
