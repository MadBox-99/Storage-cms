<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatOutbounds;

use App\Enums\IntrastatDirection;
use App\Enums\NavigationGroup;
use App\Filament\Resources\IntrastatOutbounds\Pages\ListIntrastatOutbounds;
use App\Filament\Resources\IntrastatOutbounds\Tables\IntrastatOutboundsTable;
use App\Models\IntrastatDeclaration;
use App\Services\IntrastatService;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use UnitEnum;
use ZipArchive;

final class IntrastatOutboundResource extends Resource
{
    protected static ?string $model = IntrastatDeclaration::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::INTRASTAT;

    protected static ?string $navigationLabel = 'Kiszállítás (Dispatch)';

    protected static ?string $modelLabel = 'Kiszállítás';

    protected static ?string $pluralModelLabel = 'Kiszállítások';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('direction', IntrastatDirection::DISPATCH);
    }

    public static function table(Table $table): Table
    {
        return IntrastatOutboundsTable::configure($table)
            ->headerActions([
                Action::make('export_xml')
                    ->label('XML Export (ZIP)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (IntrastatService $service): BinaryFileResponse {
                        $declarations = IntrastatDeclaration::query()
                            ->where('direction', IntrastatDirection::DISPATCH)
                            ->where('status', 'READY')
                            ->with('intrastatLines')
                            ->get();

                        $zip = new ZipArchive();
                        $zipFilename = tempnam(sys_get_temp_dir(), 'intrastat_');
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
                            'intrastat_dispatch_'.now()->format('Y-m-d_His').'.zip',
                            ['Content-Type' => 'application/zip']
                        )->deleteFileAfterSend(true);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('XML Export - Kiszállítások')
                    ->modalDescription('Az összes READY státuszú kiszállítási nyilatkozat exportálása ZIP-be csomagolt XML formátumba.')
                    ->color('success')
                    ->visible(fn (): bool => IntrastatDeclaration::query()
                        ->where('direction', IntrastatDirection::DISPATCH)
                        ->where('status', 'READY')
                        ->exists()
                    ),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIntrastatOutbounds::route('/'),
        ];
    }
}
