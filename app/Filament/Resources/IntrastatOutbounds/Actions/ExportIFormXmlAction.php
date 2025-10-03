<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatOutbounds\Actions;

use App\Enums\IntrastatDirection;
use App\Enums\IntrastatStatus;
use App\Models\IntrastatDeclaration;
use App\Services\IntrastatService;
use Carbon\Carbon;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

final class ExportIFormXmlAction
{
    public static function make(): Action
    {
        return Action::make('export_iform_xml')
            ->label('iFORM XML Export (KSH-Elektra)')
            ->icon('heroicon-o-cloud-arrow-up')
            ->fillForm([
                'period' => now()->startOfMonth(),
            ])
            ->schema([
                DatePicker::make('period')
                    ->label('Időszak')
                    ->displayFormat('Y. F')
                    ->format('Y-m')
                    ->required()
                    ->native(false),
            ])
            ->action(function (array $data, IntrastatService $service): BinaryFileResponse {
                $date = Carbon::parse($data['period']);

                $declarations = IntrastatDeclaration::query()
                    ->where('direction', IntrastatDirection::DISPATCH)
                    ->where('status', IntrastatStatus::READY)
                    ->where('reference_year', $date->year)
                    ->where('reference_month', $date->month)
                    ->with('intrastatLines')
                    ->get();

                if ($declarations->isEmpty()) {
                    throw new Exception('Nincs READY státuszú nyilatkozat a kiválasztott időszakra.');
                }

                $zip = new ZipArchive();
                $zipFilename = tempnam(sys_get_temp_dir(), 'intrastat_iform_');
                $zip->open($zipFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

                foreach ($declarations as $declaration) {
                    $xml = $service->exportToIFormXml($declaration);
                    $filename = sprintf(
                        'OSAP_2010_%s_%s.xml',
                        $declaration->declaration_number,
                        now()->format('YmdHis')
                    );
                    $zip->addFromString($filename, $xml);
                }

                $zip->close();

                return response()->download(
                    $zipFilename,
                    sprintf('intrastat_iform_dispatch_%d_%02d_%s.zip', $date->year, $date->month, now()->format('His')),
                    ['Content-Type' => 'application/zip']
                )->deleteFileAfterSend(true);
            })
            ->modalHeading('iFORM XML Export - Kiszállítások (OSAP 2010)')
            ->modalDescription('Válaszd ki az időszakot, majd exportáld a READY státuszú kiszállítási nyilatkozatokat ZIP-be csomagolt iFORM XML formátumba. Ez a formátum közvetlenül feltölthető a KSH-Elektra rendszerbe (https://elektra.ksh.hu).')
            ->modalSubmitActionLabel('Exportálás')
            ->color('primary')
            ->visible(fn (): bool => IntrastatDeclaration::query()
                ->where('direction', IntrastatDirection::DISPATCH)
                ->where('status', IntrastatStatus::READY)
                ->exists()
            );
    }
}
