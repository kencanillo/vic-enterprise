<?php

namespace App\Enums;

final class Role
{
    public const SUPER_ADMIN = 'Super Admin';
    public const ADMIN = 'Admin';
    public const OPERATIONS_LEAD = 'Operations Lead';
    public const WAREHOUSE_STAFF = 'Warehouse Staff';
    public const DISPATCH_STAFF = 'Dispatch Staff';
    public const VIEWER = 'Viewer';

    public static function all(): array
    {
        return [self::SUPER_ADMIN, self::ADMIN, self::OPERATIONS_LEAD, self::WAREHOUSE_STAFF, self::DISPATCH_STAFF, self::VIEWER];
    }

    public static function administrators(): array
    {
        return [self::SUPER_ADMIN, self::ADMIN];
    }
}