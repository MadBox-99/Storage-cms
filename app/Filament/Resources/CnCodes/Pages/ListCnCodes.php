<?php

declare(strict_types=1);

namespace App\Filament\Resources\CnCodes\Pages;

use App\Filament\Resources\CnCodes\CnCodeResource;
use App\Filament\Resources\CnCodes\Exports\CnCodeExport;
use App\Filament\Resources\CnCodes\Imports\CnCodeImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Response;

final class ListCnCodes extends ListRecords
{
    protected static string $resource = CnCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('downloadTemplate')
                ->label('Példa CSV letöltése')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $csv = "code,description,supplementary_unit\n";
                    $csv .= "85171231,Okostelefonok,darab\n";
                    $csv .= "84713010,\"Hordozható adatfeldolgozó gépek, maximum 10 kg\",darab\n";
                    $csv .= "04069050,Sajt mozzarella,kg\n";

                    return Response::streamDownload(function () use ($csv) {
                        echo $csv;
                    }, 'cn_codes_template.csv', [
                        'Content-Type' => 'text/csv',
                        'Content-Disposition' => 'attachment; filename="cn_codes_template.csv"',
                    ]);
                }),
            ImportAction::make()
                ->importer(CnCodeImport::class)
                ->label('Importálás')
                ->color('success'),
            ExportAction::make()
                ->exporter(CnCodeExport::class)
                ->label('Exportálás')
                ->color('warning'),
        ];
    }
}
