<?php

declare(strict_types=1);

namespace App\Enums;

enum UnitType: string
{
    case PIECE = 'PIECE';
    case KILOGRAM = 'KILOGRAM';
    case GRAM = 'GRAM';
    case TON = 'TON';
    case LITER = 'LITER';
    case MILLILITER = 'MILLILITER';
    case METER = 'METER';
    case CENTIMETER = 'CENTIMETER';
    case SQUARE_METER = 'SQUARE_METER';
    case CUBIC_METER = 'CUBIC_METER';
    case BOX = 'BOX';
    case PALLET = 'PALLET';
    case PACK = 'PACK';
    case BOTTLE = 'BOTTLE';
    case CAN = 'CAN';

    public function label(): string
    {
        return match ($this) {
            self::PIECE => 'Piece',
            self::KILOGRAM => 'Kilogram',
            self::GRAM => 'Gram',
            self::TON => 'Ton',
            self::LITER => 'Liter',
            self::MILLILITER => 'Milliliter',
            self::METER => 'Meter',
            self::CENTIMETER => 'Centimeter',
            self::SQUARE_METER => 'Square Meter',
            self::CUBIC_METER => 'Cubic Meter',
            self::BOX => 'Box',
            self::PALLET => 'Pallet',
            self::PACK => 'Pack',
            self::BOTTLE => 'Bottle',
            self::CAN => 'Can',
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