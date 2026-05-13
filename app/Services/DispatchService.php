<?php

namespace App\Services;

use App\Enums\DispatchStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Dispatch;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DispatchService
{
    private $audit;
    private $orders;

    public function __construct(AuditLogService $audit, SalesOrderService $orders)
    {
        $this->audit = $audit;
        $this->orders = $orders;
    }

    public function create(array $data, int $userId): Dispatch
    {
        return DB::transaction(function () use ($data, $userId) {
            $order = SalesOrder::with('items')->lockForUpdate()->findOrFail($data['sales_order_id']);
            if (! in_array($order->status, [SalesOrderStatus::APPROVED, SalesOrderStatus::PARTIALLY_DISPATCHED], true)) {
                throw ValidationException::withMessages(['sales_order_id' => 'Only approved sales orders can be dispatched.']);
            }
            $items = $data['items']; unset($data['items']);
            $data['created_by'] = $userId;
            $data['customer_id'] = $order->customer_id;
            $dispatch = Dispatch::create($data);
            foreach ($items as $item) {
                $orderItem = SalesOrderItem::where('sales_order_id', $order->id)->lockForUpdate()->findOrFail($item['sales_order_item_id']);
                $remaining = (float) $orderItem->quantity - (float) $orderItem->dispatched_quantity;
                if ((float) $item['quantity'] > $remaining) {
                    throw ValidationException::withMessages(['items' => 'Dispatch quantity exceeds remaining approved sales order quantity.']);
                }
                $dispatch->items()->create(['sales_order_item_id' => $orderItem->id, 'product_id' => $orderItem->product_id, 'quantity' => $item['quantity']]);
                $orderItem->increment('dispatched_quantity', $item['quantity']);
            }
            $this->orders->refreshDispatchStatus($order->fresh());
            $this->audit->log('dispatch.created', $dispatch, [], $dispatch->fresh('items')->toArray());
            return $dispatch->fresh('items');
        });
    }

    public function updateStatus(Dispatch $dispatch, string $status): Dispatch
    {
        $old = $dispatch->toArray();
        $data = ['status' => $status];
        if ($status === DispatchStatus::DELIVERED) $data['delivered_at'] = now();
        if ($status === DispatchStatus::CANCELLED) $data['cancelled_at'] = now();
        $dispatch->update($data);
        $this->audit->log('dispatch.status_changed', $dispatch, $old, $dispatch->fresh()->toArray());
        return $dispatch->fresh();
    }
}