<?php

namespace App\Http\Controllers;

use App\Enums\DispatchStatus;
use App\Enums\TransportMode;
use App\Http\Requests\StoreDispatchRequest;
use App\Http\Requests\UpdateDispatchRequest;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Hauler;
use App\Models\OperationalArea;
use App\Models\SalesOrder;
use App\Models\Vehicle;
use App\Services\DispatchService;
use Inertia\Inertia;

class DispatchController extends Controller
{
    public function index(){ $this->authorize('viewAny', Dispatch::class); return Inertia::render('Dispatches/Index', ['dispatches' => Dispatch::with('salesOrder','customer')->latest()->paginate(15)]); }
    public function create(){ $this->authorize('create', Dispatch::class); return Inertia::render('Dispatches/Create', $this->formData()); }
    public function store(StoreDispatchRequest $request, DispatchService $service){ $dispatch = $service->create($request->validated(), $request->user()->id); return redirect()->route('dispatches.show', $dispatch)->with('success','Dispatch record created.'); }
    public function show(Dispatch $dispatch){ $this->authorize('view', $dispatch); return Inertia::render('Dispatches/Show', ['dispatch' => $dispatch->load('salesOrder','customer','hauler','vehicle','driver','items.product')]); }
    public function edit(Dispatch $dispatch){ $this->authorize('update', $dispatch); return Inertia::render('Dispatches/Edit', array_merge($this->formData(), ['dispatch' => $dispatch->load('items')])); }
    public function update(UpdateDispatchRequest $request, Dispatch $dispatch){ $this->authorize('update', $dispatch); $dispatch->update(collect($request->validated())->except('items')->toArray()); return redirect()->route('dispatches.show', $dispatch)->with('success','Dispatch updated.'); }
    public function markInTransit(Dispatch $dispatch, DispatchService $service){ $this->authorize('update', $dispatch); $service->updateStatus($dispatch, DispatchStatus::IN_TRANSIT); return back()->with('success','Dispatch marked in transit.'); }
    public function markDelivered(Dispatch $dispatch, DispatchService $service){ $this->authorize('update', $dispatch); $service->updateStatus($dispatch, DispatchStatus::DELIVERED); return back()->with('success','Dispatch delivered.'); }
    public function cancel(Dispatch $dispatch, DispatchService $service){ $this->authorize('update', $dispatch); $service->updateStatus($dispatch, DispatchStatus::CANCELLED); return back()->with('success','Dispatch cancelled.'); }
    private function formData(): array { return ['salesOrders'=>SalesOrder::with('items.product','customer')->whereIn('status',['Approved','Partially Dispatched'])->get(), 'haulers'=>Hauler::where('is_active',1)->get(), 'vehicles'=>Vehicle::where('is_active',1)->get(), 'drivers'=>Driver::where('is_active',1)->get(), 'operationalAreas'=>OperationalArea::where('is_active',1)->get(), 'statuses'=>DispatchStatus::all(), 'transportModes'=>TransportMode::all()]; }
}