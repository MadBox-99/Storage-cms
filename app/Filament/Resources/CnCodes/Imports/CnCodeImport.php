<?php

declare(strict_types=1);

namespace App\Filament\Resources\CnCodes\Imports;

use App\Models\CnCode;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

final class CnCodeImport extends Importer
{
    protected static ?string $model = CnCode::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->label('Kód')
                ->requiredMapping()
                ->rules(['required', 'string', 'size:8', 'regex:/^[0-9]{8}$/']),

            ImportColumn::make('description')
                ->label('Megnevezés')
                ->requiredMapping()
                ->rules(['required', 'string']),

            ImportColumn::make('supplementary_unit')
                ->label('Kiegészítő mértékegység')
                ->rules(['nullable', 'string', 'max:50']),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'CN kódok importálása befejeződött. '.number_format($import->successful_rows).' sor sikeresen importálva.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' sor sikertelen.';
        }

        return $body;
    }

    public function resolveRecord(): ?CnCode
    {
        return CnCode::firstOrNew([
            'code' => $this->data['code'],
        ]);
    }
}
