<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Hauler;
use App\Models\OperationalArea;
use App\Models\PaymentTerm;
use App\Models\Product;
use App\Models\SalesAgent;
use App\Models\Vehicle;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MasterDataController extends Controller
{
    private array $map = [
        'customers' => Customer::class,
        'products' => Product::class,
        'warehouses' => Warehouse::class,
        'haulers' => Hauler::class,
        'vehicles' => Vehicle::class,
        'drivers' => Driver::class,
        'operational-areas' => OperationalArea::class,
        'payment-terms' => PaymentTerm::class,
        'sales-agents' => SalesAgent::class,
    ];

    public function index(Request $request, string $resource)
    {
        $this->guard($request); $model = $this->model($resource);
        $records = $model::query()->when($request->search, fn ($q, $v) => $q->where('name','like',"%$v%"))->latest()->paginate(15);
        return Inertia::render('MasterData/Index', ['resource' => $resource, 'title' => Str::headline($resource), 'records' => $records]);
    }

    public function store(Request $request, string $resource, AuditLogService $audit)
    {
        $this->guard($request); $model = $this->model($resource);
        $data = $request->validate($this->rules($resource));
        $record = $model::create($data);
        $audit->log('master_data.created', $record, [], $record->toArray());
        return back()->with('success','Master data record created.');
    }

    public function update(Request $request, string $resource, int $id, AuditLogService $audit)
    {
        $this->guard($request); $model = $this->model($resource); $record = $model::findOrFail($id); $old = $record->toArray();
        $record->update($request->validate($this->rules($resource, $record->id)));
        $audit->log('master_data.updated', $record, $old, $record->fresh()->toArray());
        return back()->with('success','Master data record updated.');
    }

    public function destroy(Request $request, string $resource, int $id, AuditLogService $audit)
    {
        $this->guard($request); $model = $this->model($resource); $record = $model::findOrFail($id); $old = $record->toArray();
        $record->update(['is_active' => false]); $record->delete();
        $audit->log('master_data.deactivated', $record, $old, []);
        return back()->with('success','Master data record deactivated.');
    }

    private function guard(Request $request): void { abort_unless($request->user()->hasRole([Role::SUPER_ADMIN, Role::ADMIN]), 403); }
    private function model(string $resource): string { abort_unless(isset($this->map[$resource]), 404); return $this->map[$resource]; }
    private function rules(string $resource, ?int $id = null): array
    {
        $rules = ['name' => ['required','string','max:255'], 'is_active' => ['sometimes','boolean']];
        if ($resource === 'products') $rules += ['sku' => ['nullable','string','max:255'], 'uom' => ['nullable','string','max:30']];
        if ($resource === 'warehouses') $rules += ['location' => ['nullable','string','max:255'], 'capacity' => ['nullable','integer','min:0']];
        if ($resource === 'vehicles') $rules += ['hauler_id' => ['nullable','exists:haulers,id'], 'plate_number' => ['required','string','max:255']];
        if ($resource === 'drivers') $rules += ['license_number' => ['nullable','string','max:255']];
        if ($resource === 'sales-agents') $rules += ['email' => ['nullable','email','max:255']];
        return $rules;
    }
}