<?php

namespace App\Enums;

final class TransportMode
{
    public const TRUCK = 'Truck';
    public const VESSEL = 'Vessel';

    public static function all(): array
    {
        return [self::TRUCK, self::VESSEL];
    }
}