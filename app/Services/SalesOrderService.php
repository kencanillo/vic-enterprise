<?php

namespace App\Services;

use App\Enums\SalesOrderStatus;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;

class SalesOrderService
{
    private $audit;

    public function __construct(AuditLogService $audit)
    {
        $this->audit = $audit;
    }

    public function create(array $data, int $userId): SalesOrder
    {
        return DB::transaction(function () use ($data, $userId) {
            $items = $data['items']; unset($data['items']);
            $data['created_by'] = $userId;
            $data['so_number'] = $this->nextNumber();
            $data = array_merge($data, $this->totals($items));
            $order = SalesOrder::create($data);
            $this->syncItems($order, $items);
            $this->audit->log('sales_order.created', $order, [], $order->fresh('items')->toArray());
            return $order->fresh('items');
        });
    }

    public function update(SalesOrder $order, array $data): SalesOrder
    {
        return DB::transaction(function () use ($order, $data) {
            $old = $order->load('items')->toArray();
            $items = $data['items']; unset($data['items']);
            $data = array_merge($data, $this->totals($items));
            $order->update($data);
            $order->items()->delete();
            $this->syncItems($order, $items);
            $this->audit->log('sales_order.updated', $order, $old, $order->fresh('items')->toArray());
            return $order->fresh('items');
        });
    }

    public function delete(SalesOrder $order): void
    {
        DB::transaction(function () use ($order) {
            $old = $order->load('items')->toArray();
            $order->delete();
            $this->audit->log('sales_order.deleted', $order, $old, []);
        });
    }

    private function nextNumber(): string
    {
        $next = (SalesOrder::max('id') ?? 0) + 1;
        return 'SO-' . now()->format('Y') . '-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    private function totals(array $items): array
    {
        $subtotal = collect($items)->sum(fn ($item) => round($item['quantity'] * $item['unit_price'], 2));
        return ['subtotal' => $subtotal, 'tax_total' => 0, 'grand_total' => $subtotal];
    }

    private function syncItems(SalesOrder $order, array $items): void
    {
        foreach ($items as $item) {
            $item['line_total'] = round($item['quantity'] * $item['unit_price'], 2);
            $item['dispatched_quantity'] = 0;
            $order->items()->create($item);
        }
    }

    public function refreshDispatchStatus(SalesOrder $order): void
    {
        $order->load('items');
        $total = (float) $order->items->sum('quantity');
        $dispatched = (float) $order->items->sum('dispatched_quantity');
        $status = $dispatched <= 0 ? SalesOrderStatus::APPROVED : ($dispatched >= $total ? SalesOrderStatus::FULLY_DISPATCHED : SalesOrderStatus::PARTIALLY_DISPATCHED);
        $order->update(['status' => $status]);
    }
}