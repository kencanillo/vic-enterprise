<?php

namespace App\Enums;

final class SalesOrderStatus
{
    public const DRAFT = 'Draft';
    public const PENDING_APPROVAL = 'Pending Approval';
    public const APPROVED = 'Approved';
    public const PARTIALLY_DISPATCHED = 'Partially Dispatched';
    public const FULLY_DISPATCHED = 'Fully Dispatched';
    public const CANCELLED = 'Cancelled';

    public static function all(): array
    {
        return [self::DRAFT, self::PENDING_APPROVAL, self::APPROVED, self::PARTIALLY_DISPATCHED, self::FULLY_DISPATCHED, self::CANCELLED];
    }
}