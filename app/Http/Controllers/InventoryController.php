<?php

namespace App\Http\Controllers;

use App\Enums\InventoryMovementType;
use App\Enums\InventoryQuantityType;
use App\Http\Requests\StoreInventoryUpdateRequest;
use App\Models\InventoryStock;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index(){ $this->authorize('viewAny', InventoryTransaction::class); return Inertia::render('Inventory/Index', ['stocks' => InventoryStock::with('warehouse','product')->get(), 'recent' => InventoryTransaction::with('product','warehouse')->latest()->limit(10)->get()]); }
    public function update(){ $this->authorize('create', InventoryTransaction::class); return Inertia::render('Inventory/Update', $this->formData()); }
    public function store(StoreInventoryUpdateRequest $request, InventoryService $service){ $service->update($request->validated(), $request->user()->id); return redirect()->route('inventory.index')->with('success','Inventory update submitted.'); }
    public function history(){ $this->authorize('viewAny', InventoryTransaction::class); return Inertia::render('Inventory/History', ['transactions' => InventoryTransaction::with('product','warehouse','creator')->latest()->paginate(20)]); }
    private function formData(): array { return ['warehouses'=>Warehouse::where('is_active',1)->get(), 'products'=>Product::where('is_active',1)->get(), 'quantityTypes'=>InventoryQuantityType::all(), 'movementTypes'=>InventoryMovementType::all(), 'stocks'=>InventoryStock::with('product')->get(), 'recent'=>InventoryTransaction::with('product','warehouse')->latest()->limit(8)->get()]; }
}