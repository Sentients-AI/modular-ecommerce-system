<?php

namespace App\Domain\Inventory\Enums;

enum StockMovementType: string
{
    CASE IN = 'IN';
    CASE OUT = 'OUT';
    CASE RESERVE = 'RESERVE';
    CASE RELEASE = 'RELEASE';

    public static function isValid(string $type): bool
    {
        return in_array($type, [self::IN, self::OUT, self::RESERVE, self::RELEASE]);
    }

    public static function getTypes(): array
    {
        return [
            self::IN, self::OUT, self::RESERVE, self::RELEASE
        ];
    }
}
