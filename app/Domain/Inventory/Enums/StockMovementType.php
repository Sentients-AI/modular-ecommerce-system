<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Enums;

enum StockMovementType: string
{
    case In = 'in';
    case Out = 'out';
    case Reserve = 'reserve';
    case Release = 'release';
    case Reconciliation = 'reconciliation';

    public static function isValid(string $type): bool
    {
        return in_array($type, [self::In, self::Out, self::Reserve, self::Release, self::Reconciliation]);
    }

    public static function getTypes(): array
    {
        return [
            self::In, self::Out, self::Reserve, self::Release, self::Reconciliation,
        ];
    }
}
