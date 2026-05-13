<?php

namespace Database\Seeders;

use App\Enums\DispatchStatus;
use App\Enums\InventoryMovementType;
use App\Enums\InventoryQuantityType;
use App\Enums\Role as RoleEnum;
use App\Enums\SalesOrderStatus;
use App\Enums\TransportMode;
use App\Models\Customer;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Hauler;
use App\Models\InventoryStock;
use App\Models\OperationalArea;
use App\Models\PaymentTerm;
use App\Models\Product;
use App\Models\Role;
use App\Models\SalesAgent;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RoleEnum::all() as $name) Role::firstOrCreate(['name' => $name]);
        $users = [
            ['superadmin@example.com', 'Super Admin User', RoleEnum::SUPER_ADMIN],
            ['admin@example.com', 'Admin User', RoleEnum::ADMIN],
            ['operations@example.com', 'Operations Lead', RoleEnum::OPERATIONS_LEAD],
            ['warehouse@example.com', 'Warehouse Staff', RoleEnum::WAREHOUSE_STAFF],
            ['dispatch@example.com', 'Dispatch Staff', RoleEnum::DISPATCH_STAFF],
            ['viewer@example.com', 'Viewer User', RoleEnum::VIEWER],
        ];
        foreach ($users as [$email, $name, $role]) {
            $user = User::firstOrCreate(['email' => $email], ['name' => $name, 'password' => Hash::make('password')]);
            $user->roles()->syncWithoutDetaching([Role::where('name', $role)->first()->id]);
        }

        $customers = collect(['Northern Luzon Builders','Quezon Distribution','Century Peak Plant','Makati Hardware'])->map(fn ($name) => Customer::firstOrCreate(['name' => $name]));
        $products = collect([
            ['Masonry','MAS-40','BAGS'], ['Pro','PRO-40','BAGS'], ['Prime','PRI-40','BAGS'], ['Type I Portland','TIP-40','BAGS'], ['Type IP Pozzolan','TIPZ-40','BAGS']
        ])->map(fn ($p) => Product::firstOrCreate(['name' => $p[0]], ['sku' => $p[1], 'uom' => $p[2]]));
        $warehouse = Warehouse::firstOrCreate(['name' => 'Packhouse Alpha'], ['location' => 'Terminal 04-A', 'capacity' => 250000]);
        $hauler = Hauler::firstOrCreate(['name' => 'Internal Fleet']);
        $vehicle = Vehicle::firstOrCreate(['plate_number' => 'PL-8821'], ['name' => 'Truck PL-8821', 'hauler_id' => $hauler->id]);
        $driver = Driver::firstOrCreate(['name' => 'R. Sterling'], ['license_number' => 'N01-2026']);
        $area = OperationalArea::firstOrCreate(['name' => 'Northern Luzon Hub']);
        $term = PaymentTerm::firstOrCreate(['name' => 'COD - Cash on Delivery']);
        $agent = SalesAgent::firstOrCreate(['name' => 'Assigned Agent'], ['email' => 'agent@example.com']);

        foreach ($products as $index => $product) {
            InventoryStock::updateOrCreate(['warehouse_id' => $warehouse->id, 'product_id' => $product->id], [
                'palletized_quantity' => 12000 + ($index * 4000), 'sling_quantity' => 8000 + ($index * 2000), 'tonner_quantity' => 500 + ($index * 250),
            ]);
        }

        $creator = User::where('email', 'operations@example.com')->first();
        $order = SalesOrder::firstOrCreate(['so_number' => 'SO-2026-00001'], [
            'po_date' => now()->toDateString(), 'customer_id' => $customers[0]->id, 'sales_agent_id' => $agent->id, 'payment_term_id' => $term->id,
            'location' => 'Delivery Destination', 'operational_area_id' => $area->id, 'transport_mode' => TransportMode::TRUCK,
            'truck_or_vessel_identifier' => 'PL-8821', 'status' => SalesOrderStatus::APPROVED, 'subtotal' => 245000, 'tax_total' => 0, 'grand_total' => 245000, 'created_by' => $creator->id,
        ]);
        if ($order->items()->count() === 0) {
            $order->items()->create(['product_id' => $products[3]->id, 'description' => 'Portland Cement Type I - 40kg', 'quantity' => 1000, 'uom' => 'BAGS', 'unit_price' => 245, 'line_total' => 245000, 'dispatched_quantity' => 100]);
        }
        $dispatch = Dispatch::firstOrCreate(['dr_number' => 'DR-98765-ABC'], [
            'sales_order_id' => $order->id, 'dispatch_date' => now()->toDateString(), 'status' => DispatchStatus::DELIVERED, 'customer_id' => $order->customer_id,
            'pod_location' => 'Terminal 04-A', 'hauler_id' => $hauler->id, 'vehicle_id' => $vehicle->id, 'driver_id' => $driver->id, 'balance' => 0,
            'operational_area_id' => $area->id, 'transport_mode' => TransportMode::TRUCK, 'created_by' => $creator->id, 'delivered_at' => now(),
        ]);
        if ($dispatch->items()->count() === 0) $dispatch->items()->create(['sales_order_item_id' => $order->items()->first()->id, 'product_id' => $products[3]->id, 'quantity' => 100]);
    }
}