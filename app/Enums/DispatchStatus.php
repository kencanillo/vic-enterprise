<?php

namespace App\Enums;

final class DispatchStatus
{
    public const PENDING = 'Pending Dispatch';
    public const IN_TRANSIT = 'In Transit';
    public const DELIVERED = 'Delivered';
    public const DELAYED = 'Delayed';
    public const CANCELLED = 'Cancelled';

    public static function all(): array
    {
        return [self::PENDING, self::IN_TRANSIT, self::DELIVERED, self::DELAYED, self::CANCELLED];
    }
}