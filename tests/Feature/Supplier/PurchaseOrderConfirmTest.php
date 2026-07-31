<?php

namespace Tests\Feature\Supplier;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderItemModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderModel;
use Modules\Supplier\Infrastructure\Models\SupplierModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Tests\TestCase;

class PurchaseOrderConfirmTest extends TestCase
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

    public function test_can_confirm_draft_po_with_items(): void
    {
        // Create PO with items
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

        $response = $this->actingAs($this->user)->postJson("/api/supplier/purchase-orders/{$po->id}/confirm");

        $response->assertOk();

        $this->assertDatabaseHas('supplier_purchase_orders', [
            'id' => $po->id,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_cannot_confirm_empty_po(): void
    {
        // Create PO without items
        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-001',
            'order_date' => '2026-08-01',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'total_amount' => 0,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/supplier/purchase-orders/{$po->id}/confirm");

        $response->assertStatus(422);

        // Status should remain draft
        $this->assertDatabaseHas('supplier_purchase_orders', [
            'id' => $po->id,
            'status' => 'draft',
        ]);
    }

    public function test_cannot_edit_confirmed_po(): void
    {
        // Create a confirmed PO
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

        $response = $this->actingAs($this->user)->putJson("/api/supplier/purchase-orders/{$po->id}", [
            'supplier_id' => $this->supplier->id,
            'order_date' => '2026-08-02',
            'items' => [
                [
                    'product_variant_id' => 1,
                    'product_name' => 'Kopi Arabika',
                    'variant_name' => '1kg',
                    'quantity' => 20,
                    'unit_cost' => 55000,
                ],
            ],
        ]);

        $response->assertStatus(422);
    }
}
