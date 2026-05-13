<?php

namespace App\Services;

use App\Enums\DispatchStatus;
use App\Enums\SalesOrderStatus;
use App\Models\AuditLog;
use App\Models\Dispatch;
use App\Models\InventoryStock;
use App\Models\SalesOrder;
use Carbon\Carbon;

class DashboardService
{
    public function data(): array
    {
        $now = now();
        $mtd = $this->deliveredBetween($now->copy()->startOfMonth(), $now);
        $lastMonth = $this->deliveredBetween($now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth());
        $ytd = $this->deliveredBetween($now->copy()->startOfYear(), $now);
        $target = 5000000;
        $inventory = InventoryStock::with('product')->get()->map(fn ($stock) => [
            'cement_type' => $stock->product->name,
            'palletized_quantity' => (float) $stock->palletized_quantity,
            'sling_quantity' => (float) $stock->sling_quantity,
            'tonner_quantity' => (float) $stock->tonner_quantity,
            'total' => $stock->total(),
            'status' => $stock->total() > 1000 ? 'Healthy' : 'Low',
        ]);
        $totalInventory = $inventory->sum('total');
        return [
            'kpis' => [
                'mtd_volume' => $mtd,
                'last_month_volume' => $lastMonth,
                'ytd_volume' => $ytd,
                'annual_target_progress' => round(($ytd / $target) * 100, 1),
                'total_inventory' => $totalInventory,
                'active_dispatches' => Dispatch::whereIn('status', [DispatchStatus::PENDING, DispatchStatus::IN_TRANSIT, DispatchStatus::DELAYED])->count(),
                'pending_sales_orders' => SalesOrder::where('status', SalesOrderStatus::PENDING_APPROVAL)->count(),
                'completed_deliveries' => Dispatch::where('status', DispatchStatus::DELIVERED)->count(),
            ],
            'inventory' => $inventory,
            'fleet_status' => Dispatch::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'capacity' => ['used' => min(100, round(($totalInventory / 250000) * 100, 1)), 'label' => 'Storage capacity'],
            'recent_activity' => AuditLog::latest()->limit(8)->get(['action','created_at']),
        ];
    }

    private function deliveredBetween(Carbon $start, Carbon $end): float
    {
        return (float) Dispatch::where('status', DispatchStatus::DELIVERED)->whereBetween('dispatch_date', [$start->toDateString(), $end->toDateString()])->with('items')->get()->sum(fn ($dispatch) => $dispatch->items->sum('quantity'));
    }
}