<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum UnitType: string implements HasLabel
{
    case PIECE = 'piece';
    case KILOGRAM = 'kilogram';
    case GRAM = 'gram';
    case TON = 'ton';
    case LITER = 'liter';
    case MILLILITER = 'milliliter';
    case METER = 'meter';
    case CENTIMETER = 'centimeter';
    case SQUARE_METER = 'square_meter';
    case CUBIC_METER = 'cubic_meter';
    case BOX = 'box';
    case PALLET = 'pallet';
    case PACK = 'pack';
    case BOTTLE = 'bottle';
    case CAN = 'can';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::PIECE => __('Piece'),
            self::KILOGRAM => __('Kilogram'),
            self::GRAM => __('Gram'),
            self::TON => __('Ton'),
            self::LITER => __('Liter'),
            self::MILLILITER => __('Milliliter'),
            self::METER => __('Meter'),
            self::CENTIMETER => __('Centimeter'),
            self::SQUARE_METER => __('Square Meter'),
            self::CUBIC_METER => __('Cubic Meter'),
            self::BOX => __('Box'),
            self::PALLET => __('Pallet'),
            self::PACK => __('Pack'),
            self::BOTTLE => __('Bottle'),
            self::CAN => __('Can'),
        };
    }

    public function abbreviation(): string
    {
        return match ($this) {
            self::PIECE => 'pc',
            self::KILOGRAM => 'kg',
            self::GRAM => 'g',
            self::TON => 't',
            self::LITER => 'l',
            self::MILLILITER => 'ml',
            self::METER => 'm',
            self::CENTIMETER => 'cm',
            self::SQUARE_METER => 'm²',
            self::CUBIC_METER => 'm³',
            self::BOX => 'box',
            self::PALLET => 'plt',
            self::PACK => 'pack',
            self::BOTTLE => 'btl',
            self::CAN => 'can',
        };
    }
}
