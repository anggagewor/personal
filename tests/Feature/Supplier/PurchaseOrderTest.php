<?php

namespace Tests\Feature\Supplier;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderModel;
use Modules\Supplier\Infrastructure\Models\SupplierModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
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

    public function test_can_create_purchase_order_draft(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/supplier/outlets/{$this->outlet->id}/purchase-orders", [
            'supplier_id' => $this->supplier->id,
            'order_date' => '2026-08-01',
            'expected_delivery_date' => '2026-08-05',
            'notes' => 'PO pertama',
            'items' => [
                [
                    'product_variant_id' => 1,
                    'product_name' => 'Kopi Arabika',
                    'variant_name' => '1kg',
                    'quantity' => 10,
                    'unit_cost' => 50000,
                ],
                [
                    'product_variant_id' => 2,
                    'product_name' => 'Gula Pasir',
                    'variant_name' => '5kg',
                    'quantity' => 5,
                    'unit_cost' => 75000,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $data = $response->json('data');
        $this->assertEquals('draft', $data['status']);
        $this->assertNotEmpty($data['po_number']);
        $this->assertCount(2, $data['items']);

        $this->assertDatabaseHas('supplier_purchase_orders', [
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);
    }

    public function test_can_update_draft_po(): void
    {
        // Create a PO first
        $createResponse = $this->actingAs($this->user)->postJson("/api/supplier/outlets/{$this->outlet->id}/purchase-orders", [
            'supplier_id' => $this->supplier->id,
            'order_date' => '2026-08-01',
            'items' => [
                [
                    'product_variant_id' => 1,
                    'product_name' => 'Kopi Arabika',
                    'variant_name' => '1kg',
                    'quantity' => 10,
                    'unit_cost' => 50000,
                ],
            ],
        ]);

        $poId = $createResponse->json('data.id');

        // Create a second supplier for updating
        $newSupplier = SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'PT Supplier Baru',
        ]);

        // Update the PO
        $response = $this->actingAs($this->user)->putJson("/api/supplier/purchase-orders/{$poId}", [
            'supplier_id' => $newSupplier->id,
            'order_date' => '2026-08-02',
            'expected_delivery_date' => '2026-08-10',
            'notes' => 'Updated notes',
            'items' => [
                [
                    'product_variant_id' => 3,
                    'product_name' => 'Teh Hijau',
                    'variant_name' => '500g',
                    'quantity' => 20,
                    'unit_cost' => 30000,
                ],
                [
                    'product_variant_id' => 4,
                    'product_name' => 'Susu UHT',
                    'variant_name' => '1 liter',
                    'quantity' => 15,
                    'unit_cost' => 18000,
                ],
            ],
        ]);

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals($newSupplier->id, $data['supplier_id']);
        $this->assertCount(2, $data['items']);
    }

    public function test_total_amount_is_calculated(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/supplier/outlets/{$this->outlet->id}/purchase-orders", [
            'supplier_id' => $this->supplier->id,
            'order_date' => '2026-08-01',
            'items' => [
                [
                    'product_variant_id' => 1,
                    'product_name' => 'Kopi Arabika',
                    'variant_name' => '1kg',
                    'quantity' => 10,
                    'unit_cost' => 50000,
                ],
                [
                    'product_variant_id' => 2,
                    'product_name' => 'Gula Pasir',
                    'variant_name' => '5kg',
                    'quantity' => 5,
                    'unit_cost' => 75000,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $data = $response->json('data');
        // 10 × 50000 + 5 × 75000 = 500000 + 375000 = 875000
        $this->assertEquals(875000, (float) $data['total_amount']);
    }

    public function test_po_number_format(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/supplier/outlets/{$this->outlet->id}/purchase-orders", [
            'supplier_id' => $this->supplier->id,
            'order_date' => '2026-08-01',
            'items' => [
                [
                    'product_variant_id' => 1,
                    'product_name' => 'Kopi Arabika',
                    'variant_name' => '1kg',
                    'quantity' => 5,
                    'unit_cost' => 50000,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $poNumber = $response->json('data.po_number');
        // Verify format: PO-YYYYMMDD-001
        $this->assertMatchesRegularExpression('/^PO-\d{8}-\d{3}$/', $poNumber);
    }
}
