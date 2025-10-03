<?php

declare(strict_types=1);

namespace App\Filament\Resources\CnCodes\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class CnCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kód')
                    ->length(8)
                    ->placeholder('12345678')
                    ->helperText('8 jegyű kombinált nomenklatúra kód')
                    ->required()
                    ->unique(ignoreRecord: true),

                Textarea::make('description')
                    ->label('Megnevezés')
                    ->rows(3)
                    ->placeholder('Termék megnevezése magyarul')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('supplementary_unit')
                    ->label('Kiegészítő mértékegység')
                    ->maxLength(50)
                    ->placeholder('pl. liter, darab, kg')
                    ->helperText('Ha szükséges a CN kódhoz (opcionális)'),
            ]);
    }
}
