<?php

namespace App\Enums;

final class InventoryQuantityType
{
    public const PALLETIZED = 'palletized';
    public const SLING = 'sling';
    public const TONNER = 'tonner';

    public static function all(): array
    {
        return [self::PALLETIZED, self::SLING, self::TONNER];
    }
}