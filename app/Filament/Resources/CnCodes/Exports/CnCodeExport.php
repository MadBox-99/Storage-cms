<?php

declare(strict_types=1);

namespace App\Filament\Resources\CnCodes\Exports;

use App\Models\CnCode;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

final class CnCodeExport extends Exporter
{
    protected static ?string $model = CnCode::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('code')
                ->label('Kód'),

            ExportColumn::make('description')
                ->label('Megnevezés'),

            ExportColumn::make('supplementary_unit')
                ->label('Kiegészítő mértékegység'),

            ExportColumn::make('intrastat_lines_count')
                ->label('Használat (db)')
                ->counts('intrastatLines'),

            ExportColumn::make('created_at')
                ->label('Létrehozva'),

            ExportColumn::make('updated_at')
                ->label('Módosítva'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'CN kódok exportálása befejeződött. '.number_format($export->successful_rows).' sor sikeresen exportálva.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' sor sikertelen.';
        }

        return $body;
    }
}
