<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CnCode;
use Illuminate\Database\Seeder;

final class CnCodeSeeder extends Seeder
{
    /**
     * Seed the CN codes table with sample data.
     *
     * TODO: Import actual CN nomenclature from official EU sources.
     * Current implementation uses sample data for common product categories.
     */
    public function run(): void
    {
        $cnCodes = [
            // Elektronikai termékek
            [
                'code' => '85171231',
                'description' => 'Okostelefonok',
                'supplementary_unit' => 'darab',
            ],
            [
                'code' => '85171100',
                'description' => 'Vezeték nélküli telefonok',
                'supplementary_unit' => 'darab',
            ],

            // Számítógépek és tartozékok
            [
                'code' => '84713010',
                'description' => 'Hordozható adatfeldolgozó gépek, maximum 10 kg',
                'supplementary_unit' => 'darab',
            ],
            [
                'code' => '84733020',
                'description' => 'Számítógép billentyűzetek',
                'supplementary_unit' => 'darab',
            ],

            // Ipari gépek
            [
                'code' => '84314310',
                'description' => 'Targoncák emelőmagassággal >= 1 m',
                'supplementary_unit' => 'darab',
            ],

            // Textil termékek
            [
                'code' => '62052000',
                'description' => 'Férfi vagy fiú ingek pamutból',
                'supplementary_unit' => 'darab',
            ],
            [
                'code' => '61091000',
                'description' => 'Pulóverek, kardigánok pamutból',
                'supplementary_unit' => 'darab',
            ],

            // Élelmiszerek
            [
                'code' => '04069050',
                'description' => 'Sajt mozzarella',
                'supplementary_unit' => 'kg',
            ],
            [
                'code' => '22042176',
                'description' => 'Bor palackban ≤ 2 l, Piemont',
                'supplementary_unit' => 'liter',
            ],

            // Autóalkatrészek
            [
                'code' => '87089210',
                'description' => 'Hangtompítók és kipufogócsövek személygépkocsikhoz',
                'supplementary_unit' => 'darab',
            ],
            [
                'code' => '87083010',
                'description' => 'Szerelt fékbetétek személygépkocsikhoz',
                'supplementary_unit' => 'darab',
            ],
        ];

        foreach ($cnCodes as $cnCode) {
            CnCode::create($cnCode);
        }
    }
}
