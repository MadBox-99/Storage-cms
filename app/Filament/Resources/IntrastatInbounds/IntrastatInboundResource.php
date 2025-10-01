<?php

declare(strict_types=1);

namespace App\Filament\Resources\IntrastatInbounds;

use App\Enums\IntrastatDirection;
use App\Enums\NavigationGroup;
use App\Filament\Resources\IntrastatInbounds\Pages\ListIntrastatInbounds;
use App\Filament\Resources\IntrastatInbounds\Tables\IntrastatInboundsTable;
use App\Models\IntrastatDeclaration;
use App\Services\IntrastatService;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use UnitEnum;
use ZipArchive;

final class IntrastatInboundResource extends Resource
{
    protected static ?string $model = IntrastatDeclaration::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::INTRASTAT;

    protected static ?string $navigationLabel = 'Beszállítás (Arrival)';

    protected static ?string $modelLabel = 'Beszállítás';

    protected static ?string $pluralModelLabel = 'Beszállítások';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('direction', IntrastatDirection::ARRIVAL);
    }

    public static function table(Table $table): Table
    {
        return IntrastatInboundsTable::configure($table)
            ->headerActions([
                Action::make('export_xml')
                    ->label('XML Export (ZIP)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (IntrastatService $service): BinaryFileResponse {
                        $declarations = IntrastatDeclaration::query()
                            ->where('direction', IntrastatDirection::ARRIVAL)
                            ->where('status', 'READY')
                            ->with('intrastatLines')
                            ->get();

                        $zip = new ZipArchive();
                        $zipFilename = tempnam(sys_get_temp_dir(), 'intrastat_');
                        $zip->open($zipFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

                        foreach ($declarations as $declaration) {
                            $xml = $service->exportToXml($declaration);
                            $filename = sprintf(
                                'ARRIVAL_%s_%s.xml',
                                $declaration->declaration_number,
                                now()->format('YmdHis')
                            );
                            $zip->addFromString($filename, $xml);
                        }

                        $zip->close();

                        return response()->download(
                            $zipFilename,
                            'intrastat_arrival_'.now()->format('Y-m-d_His').'.zip',
                            ['Content-Type' => 'application/zip']
                        )->deleteFileAfterSend(true);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('XML Export - Beszállítások')
                    ->modalDescription('Az összes READY státuszú beszállítási nyilatkozat exportálása ZIP-be csomagolt XML formátumba.')
                    ->color('success')
                    ->visible(fn (): bool => IntrastatDeclaration::query()
                        ->where('direction', IntrastatDirection::ARRIVAL)
                        ->where('status', 'READY')
                        ->exists()
                    ),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIntrastatInbounds::route('/'),
        ];
    }
}
