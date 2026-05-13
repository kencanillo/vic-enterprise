<?php

namespace App\Http\Controllers;

use App\Enums\SalesOrderStatus;
use App\Enums\TransportMode;
use App\Http\Requests\StoreSalesOrderRequest;
use App\Http\Requests\UpdateSalesOrderRequest;
use App\Models\Customer;
use App\Models\OperationalArea;
use App\Models\PaymentTerm;
use App\Models\Product;
use App\Models\SalesAgent;
use App\Models\SalesOrder;
use App\Services\AuditLogService;
use App\Services\SalesOrderService;
use Inertia\Inertia;

class SalesOrderController extends Controller
{
    public function index(){ $this->authorize('viewAny', SalesOrder::class); return Inertia::render('SalesOrders/Index', ['orders' => SalesOrder::with('customer')->latest()->paginate(15)]); }
    public function create(){ $this->authorize('create', SalesOrder::class); return Inertia::render('SalesOrders/Create', $this->formData()); }
    public function store(StoreSalesOrderRequest $request, SalesOrderService $service){ $order = $service->create($request->validated(), $request->user()->id); return redirect()->route('sales-orders.show', $order)->with('success','Sales order created.'); }
    public function show(SalesOrder $salesOrder){ $this->authorize('view', $salesOrder); return Inertia::render('SalesOrders/Show', ['order' => $salesOrder->load('customer','items.product','dispatches')]); }
    public function edit(SalesOrder $salesOrder){ $this->authorize('update', $salesOrder); return Inertia::render('SalesOrders/Edit', array_merge($this->formData(), ['order' => $salesOrder->load('items')])); }
    public function update(UpdateSalesOrderRequest $request, SalesOrder $salesOrder, SalesOrderService $service){ $service->update($salesOrder, $request->validated()); return redirect()->route('sales-orders.show', $salesOrder)->with('success','Sales order updated.'); }
    public function destroy(SalesOrder $salesOrder, SalesOrderService $service){ $this->authorize('delete', $salesOrder); $service->delete($salesOrder); return redirect()->route('sales-orders.index')->with('success','Sales order deleted.'); }
    public function printLog(SalesOrder $salesOrder, AuditLogService $audit){ $this->authorize('view', $salesOrder); $audit->log('sales_order.printed', $salesOrder, [], ['so_number' => $salesOrder->so_number]); return back()->with('success','Print action logged.'); }
    private function formData(): array { return ['customers'=>Customer::where('is_active',1)->get(), 'products'=>Product::where('is_active',1)->get(), 'paymentTerms'=>PaymentTerm::where('is_active',1)->get(), 'salesAgents'=>SalesAgent::where('is_active',1)->get(), 'operationalAreas'=>OperationalArea::where('is_active',1)->get(), 'statuses'=>SalesOrderStatus::all(), 'transportModes'=>TransportMode::all()]; }
}