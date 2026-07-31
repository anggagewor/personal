<?php

namespace Tests\Feature\Supplier;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderItemModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderModel;
use Modules\Supplier\Infrastructure\Models\SupplierModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Tests\TestCase;

class PurchaseOrderCancelTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private OutletModel $outlet;
    private SupplierModel $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->outlet = OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Test Outlet',
            'business_type' => 'retail',
        ]);
        $this->supplier = SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'PT Supplier Utama',
        ]);
    }

    public function test_can_cancel_draft_po(): void
    {
        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-001',
            'order_date' => '2026-08-01',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'total_amount' => 500000,
        ]);

        PurchaseOrderItemModel::create([
            'purchase_order_id' => $po->id,
            'product_variant_id' => 1,
            'product_name' => 'Kopi Arabika',
            'variant_name' => '1kg',
            'quantity' => 10,
            'unit_cost' => 50000,
            'subtotal' => 500000,
            'received_quantity' => 0,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/supplier/purchase-orders/{$po->id}/cancel");

        $response->assertOk();

        $this->assertDatabaseHas('supplier_purchase_orders', [
            'id' => $po->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_can_cancel_confirmed_po(): void
    {
        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-001',
            'order_date' => '2026-08-01',
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'total_amount' => 500000,
        ]);

        PurchaseOrderItemModel::create([
            'purchase_order_id' => $po->id,
            'product_variant_id' => 1,
            'product_name' => 'Kopi Arabika',
            'variant_name' => '1kg',
            'quantity' => 10,
            'unit_cost' => 50000,
            'subtotal' => 500000,
            'received_quantity' => 0,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/supplier/purchase-orders/{$po->id}/cancel");

        $response->assertOk();

        $this->assertDatabaseHas('supplier_purchase_orders', [
            'id' => $po->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_cannot_cancel_partial_po(): void
    {
        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-001',
            'order_date' => '2026-08-01',
            'status' => 'partial',
            'payment_status' => 'unpaid',
            'total_amount' => 500000,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/supplier/purchase-orders/{$po->id}/cancel");

        $response->assertStatus(422);

        // Status should remain partial
        $this->assertDatabaseHas('supplier_purchase_orders', [
            'id' => $po->id,
            'status' => 'partial',
        ]);
    }

    public function test_cannot_cancel_received_po(): void
    {
        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-001',
            'order_date' => '2026-08-01',
            'status' => 'received',
            'payment_status' => 'unpaid',
            'total_amount' => 500000,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/supplier/purchase-orders/{$po->id}/cancel");

        $response->assertStatus(422);

        // Status should remain received
        $this->assertDatabaseHas('supplier_purchase_orders', [
            'id' => $po->id,
            'status' => 'received',
        ]);
    }
}
