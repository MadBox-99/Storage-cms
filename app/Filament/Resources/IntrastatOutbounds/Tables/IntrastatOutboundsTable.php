<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatOutbounds\Tables;

use App\Enums\IntrastatStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class IntrastatOutboundsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('declaration_number')
                    ->label('Nyilatkozat szám')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reference_year')
                    ->label('Év')
                    ->sortable(),
                TextColumn::make('reference_month')
                    ->label('Hónap')
                    ->sortable(),
                TextColumn::make('declaration_date')
                    ->label('Nyilatkozat dátuma')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Státusz')
                    ->badge()
                    ->color(fn (IntrastatStatus $state): string => match ($state) {
                        IntrastatStatus::DRAFT => 'gray',
                        IntrastatStatus::READY => 'success',
                        IntrastatStatus::SUBMITTED => 'info',
                        default => 'warning',
                    }),
                TextColumn::make('total_invoice_value')
                    ->label('Összérték')
                    ->money('HUF')
                    ->sortable(),
                TextColumn::make('intrastatLines_count')
                    ->label('Tételek száma')
                    ->counts('intrastatLines'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('declaration_date', 'desc');
    }
}
