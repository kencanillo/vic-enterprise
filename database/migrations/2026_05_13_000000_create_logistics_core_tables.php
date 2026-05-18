<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogisticsCoreTables extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('role_user')) {
            Schema::create('role_user', function (Blueprint $table) {
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->primary(['role_id', 'user_id']);
            });
        }

        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) { $this->master($table); $table->string('branch')->nullable(); });
        }
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) { $this->master($table); $table->string('sku')->nullable()->unique(); $table->string('uom')->default('BAGS'); });
        }
        if (!Schema::hasTable('warehouses')) {
            Schema::create('warehouses', function (Blueprint $table) { $this->master($table); $table->string('location')->nullable(); $table->unsignedInteger('capacity')->default(0); });
        }
        if (!Schema::hasTable('haulers')) {
            Schema::create('haulers', function (Blueprint $table) { $this->master($table); });
        }
        if (!Schema::hasTable('drivers')) {
            Schema::create('drivers', function (Blueprint $table) { $this->master($table); $table->string('license_number')->nullable(); });
        }
        if (!Schema::hasTable('operational_areas')) {
            Schema::create('operational_areas', function (Blueprint $table) { $this->master($table); });
        }
        if (!Schema::hasTable('payment_terms')) {
            Schema::create('payment_terms', function (Blueprint $table) { $this->master($table); });
        }
        if (!Schema::hasTable('sales_agents')) {
            Schema::create('sales_agents', function (Blueprint $table) { $this->master($table); $table->string('email')->nullable(); });
        }

        if (!Schema::hasTable('vehicles')) {
            Schema::create('vehicles', function (Blueprint $table) {
                $this->master($table);
                $table->foreignId('hauler_id')->nullable()->constrained()->nullOnDelete();
                $table->string('plate_number')->unique();
            });
        }

        if (!Schema::hasTable('sales_orders')) {
            Schema::create('sales_orders', function (Blueprint $table) {
                $table->id();
                $table->string('so_number')->unique();
                $table->date('po_date');
                $table->foreignId('customer_id')->constrained();
                $table->foreignId('sales_agent_id')->nullable()->constrained();
                $table->foreignId('payment_term_id')->constrained();
                $table->string('location')->nullable();
                $table->foreignId('operational_area_id')->constrained();
                $table->string('transport_mode');
                $table->string('truck_or_vessel_identifier')->nullable();
                $table->string('status');
                $table->decimal('subtotal', 14, 2)->default(0);
                $table->decimal('tax_total', 14, 2)->default(0);
                $table->decimal('grand_total', 14, 2)->default(0);
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('users');
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sales_order_items')) {
            Schema::create('sales_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained();
                $table->string('description')->nullable();
                $table->decimal('quantity', 14, 2);
                $table->string('uom')->default('BAGS');
                $table->decimal('unit_price', 14, 2);
                $table->decimal('line_total', 14, 2);
                $table->decimal('dispatched_quantity', 14, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dispatches')) {
            Schema::create('dispatches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sales_order_id')->constrained();
                $table->string('dr_number')->unique();
                $table->date('dispatch_date');
                $table->string('status');
                $table->foreignId('customer_id')->constrained();
                $table->string('pod_location');
                $table->foreignId('hauler_id')->constrained();
                $table->foreignId('vehicle_id')->constrained();
                $table->foreignId('driver_id')->constrained();
                $table->decimal('balance', 14, 2)->default(0);
                $table->foreignId('operational_area_id')->constrained();
                $table->string('transport_mode');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('users');
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dispatch_items')) {
            Schema::create('dispatch_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dispatch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sales_order_item_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained();
                $table->decimal('quantity', 14, 2);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('inventory_stocks')) {
            Schema::create('inventory_stocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_id')->constrained();
                $table->foreignId('product_id')->constrained();
                $table->decimal('palletized_quantity', 14, 2)->default(0);
                $table->decimal('sling_quantity', 14, 2)->default(0);
                $table->decimal('tonner_quantity', 14, 2)->default(0);
                $table->timestamps();
                $table->unique(['warehouse_id', 'product_id']);
            });
        }

        if (!Schema::hasTable('inventory_transactions')) {
            Schema::create('inventory_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_id')->constrained();
                $table->foreignId('product_id')->constrained();
                $table->string('quantity_type');
                $table->string('movement_type');
                $table->decimal('quantity', 14, 2);
                $table->decimal('previous_quantity', 14, 2);
                $table->decimal('new_quantity', 14, 2);
                $table->decimal('discrepancy_percentage', 8, 2)->default(0);
                $table->boolean('flagged_for_review')->default(false);
                $table->text('remarks')->nullable();
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action');
                $table->string('auditable_type')->nullable();
                $table->unsignedBigInteger('auditable_id')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->ipAddress('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
                $table->index(['auditable_type', 'auditable_id']);
            });
        }

        if (!Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (['system_settings','audit_logs','inventory_transactions','inventory_stocks','dispatch_items','dispatches','sales_order_items','sales_orders','vehicles','sales_agents','payment_terms','operational_areas','drivers','haulers','warehouses','products','customers','role_user','roles'] as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function master(Blueprint $table): void
    {
        $table->id();
        $table->string('name');
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
    }
}