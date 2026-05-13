<?php

namespace App\Services;

use App\Models\Dispatch;
use App\Models\InventoryTransaction;
use App\Models\SalesOrder;

class ReportService
{
    public function salesOrders(array $filters)
    {
        return SalesOrder::with('customer')->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))->when($filters['customer_id'] ?? null, fn ($q, $v) => $q->where('customer_id', $v))->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('po_date', '>=', $v))->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('po_date', '<=', $v))->latest()->get();
    }
    public function dispatches(array $filters)
    {
        return Dispatch::with('customer')->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('dispatch_date', '>=', $v))->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('dispatch_date', '<=', $v))->latest()->get();
    }
    public function inventory(array $filters)
    {
        return InventoryTransaction::with('product','warehouse')->when($filters['product_id'] ?? null, fn ($q, $v) => $q->where('product_id', $v))->latest()->get();
    }
}