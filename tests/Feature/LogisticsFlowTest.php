<?php

namespace Tests\Feature;

use App\Enums\InventoryMovementType;
use App\Enums\InventoryQuantityType;
use App\Enums\SalesOrderStatus;
use App\Enums\TransportMode;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Hauler;
use App\Models\InventoryStock;
use App\Models\InventoryTransaction;
use App\Models\OperationalArea;
use App\Models\PaymentTerm;
use App\Models\Product;
use App\Models\Role;
use App\Models\SalesAgent;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogisticsFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_authorized_users_can_access_dashboard(): void
    {
        $this->actingAs($this->admin)->get('/dashboard')->assertOk();
    }

    public function test_viewer_cannot_access_sales_order_create(): void
    {
        $viewer = User::where('email', 'viewer@example.com')->first();
        $this->actingAs($viewer)->get('/sales-orders/create')->assertForbidden();
    }

    public function test_sales_order_can_be_created_with_server_side_total(): void
    {
        $payload = $this->salesOrderPayload();
        $this->actingAs($this->admin)->post('/sales-orders', $payload)->assertRedirect();
        $order = SalesOrder::where('so_number', '!=', 'SO-2026-00001')->first();
        $this->assertEquals(490, (float) $order->grand_total);
        $this->assertDatabaseHas('audit_logs', ['action' => 'sales_order.created']);
    }

    public function test_sales_order_requires_items(): void
    {
        $payload = $this->salesOrderPayload();
        $payload['items'] = [];
        $this->actingAs($this->admin)->post('/sales-orders', $payload)->assertSessionHasErrors('items');
    }

    public function test_dispatch_cannot_exceed_available_quantity(): void
    {
        $order = SalesOrder::with('items')->where('status', SalesOrderStatus::APPROVED)->first();
        $payload = $this->dispatchPayload($order, 999999);
        $this->actingAs($this->admin)->post('/dispatches', $payload)->assertSessionHasErrors('items');
    }

    public function test_inventory_correction_over_five_percent_is_flagged(): void
    {
        $stock = InventoryStock::first();
        $payload = ['warehouse_id' => $stock->warehouse_id, 'product_id' => $stock->product_id, 'quantity_type' => InventoryQuantityType::PALLETIZED, 'movement_type' => InventoryMovementType::CORRECTION, 'quantity' => 1, 'remarks' => 'Physical count variance'];
        $this->actingAs($this->admin)->post('/inventory/update', $payload)->assertRedirect();
        $this->assertTrue(InventoryTransaction::latest()->first()->flagged_for_review);
    }

    public function test_reports_export_csv(): void
    {
        $this->actingAs($this->admin)->get('/reports/export?type=sales-orders')->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    private function salesOrderPayload(): array
    {
        return [
            'po_date' => now()->toDateString(), 'status' => SalesOrderStatus::PENDING_APPROVAL, 'customer_id' => Customer::first()->id,
            'sales_agent_id' => SalesAgent::first()->id, 'payment_term_id' => PaymentTerm::first()->id, 'location' => 'Branch A',
            'operational_area_id' => OperationalArea::first()->id, 'transport_mode' => TransportMode::TRUCK, 'truck_or_vessel_identifier' => 'PL-1000',
            'items' => [['product_id' => Product::first()->id, 'description' => 'Test cement', 'quantity' => 2, 'uom' => 'BAGS', 'unit_price' => 245]],
        ];
    }

    private function dispatchPayload(SalesOrder $order, float $quantity): array
    {
        return [
            'sales_order_id' => $order->id, 'dr_number' => 'DR-TEST-' . uniqid(), 'dispatch_date' => now()->toDateString(), 'status' => 'Pending Dispatch',
            'pod_location' => 'Plant A', 'hauler_id' => Hauler::first()->id, 'vehicle_id' => Vehicle::first()->id, 'driver_id' => Driver::first()->id,
            'balance' => 0, 'operational_area_id' => OperationalArea::first()->id, 'transport_mode' => TransportMode::TRUCK,
            'items' => [['sales_order_item_id' => $order->items->first()->id, 'quantity' => $quantity]],
        ];
    }
}
