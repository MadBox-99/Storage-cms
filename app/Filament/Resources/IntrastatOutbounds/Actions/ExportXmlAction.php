<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatOutbounds\Actions;

use App\Enums\IntrastatDirection;
use App\Enums\IntrastatStatus;
use App\Models\IntrastatDeclaration;
use App\Services\IntrastatService;
use Filament\Actions\Action;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

final class ExportXmlAction
{
    public static function make(): Action
    {
        return Action::make('export_xml')
            ->label('XML Export (Egyszerűsített)')
            ->icon('heroicon-o-arrow-down-tray')
            ->action(function (IntrastatService $service): BinaryFileResponse {
                $declarations = IntrastatDeclaration::query()
                    ->where('direction', IntrastatDirection::DISPATCH)
                    ->where('status', IntrastatStatus::READY)
                    ->with('intrastatLines')
                    ->get();

                $zip = new ZipArchive();
                $zipFilename = tempnam(sys_get_temp_dir(), 'intrastat_simple_');
                $zip->open($zipFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

                foreach ($declarations as $declaration) {
                    $xml = $service->exportToXml($declaration);
                    $filename = sprintf(
                        'DISPATCH_%s_%s.xml',
                        $declaration->declaration_number,
                        now()->format('YmdHis')
                    );
                    $zip->addFromString($filename, $xml);
                }

                $zip->close();

                return response()->download(
                    $zipFilename,
                    'intrastat_simple_dispatch_'.now()->format('Y-m-d_His').'.zip',
                    ['Content-Type' => 'application/zip']
                )->deleteFileAfterSend(true);
            })
            ->requiresConfirmation()
            ->modalHeading('Egyszerűsített XML Export - Kiszállítások')
            ->modalDescription('Az összes READY státuszú kiszállítási nyilatkozat exportálása egyszerűsített, olvasható XML formátumba. Ez a formátum dokumentációs célra készült, NEM tölthető fel a KSH-Elektra rendszerbe.')
            ->color('success')
            ->visible(fn (): bool => IntrastatDeclaration::query()
                ->where('direction', IntrastatDirection::DISPATCH)
                ->where('status', IntrastatStatus::READY)
                ->exists()
            );
    }
}
