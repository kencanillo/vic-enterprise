<?php

namespace App\Enums;

final class InventoryMovementType
{
    public const ADJUSTMENT = 'adjustment';
    public const RECEIVED = 'received';
    public const DISPATCHED = 'dispatched';
    public const CORRECTION = 'correction';

    public static function all(): array
    {
        return [self::ADJUSTMENT, self::RECEIVED, self::DISPATCHED, self::CORRECTION];
    }
}