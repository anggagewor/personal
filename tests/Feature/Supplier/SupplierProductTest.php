<?php

namespace Tests\Feature\Supplier;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Pos\Infrastructure\Models\CategoryModel;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Pos\Infrastructure\Models\ProductModel;
use Modules\Pos\Infrastructure\Models\ProductVariantModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderItemModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderModel;
use Modules\Supplier\Infrastructure\Models\SupplierModel;
use Modules\Supplier\Infrastructure\Models\SupplierProductModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Tests\TestCase;

class SupplierProductTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private OutletModel $outlet;
    private SupplierModel $supplier;
    private ProductVariantModel $variant;

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
            'name' => 'Supplier Test',
        ]);

        $category = CategoryModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Test Category',
            'sort_order' => 0,
        ]);

        $product = ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'base_price' => 10000,
            'status' => 'active',
            'track_stock' => true,
        ]);

        $this->variant = ProductVariantModel::create([
            'product_id' => $product->id,
            'name' => 'Default',
            'price' => 10000,
            'stock_quantity' => 50,
        ]);
    }

    public function test_can_link_product(): void
    {
        $response = $this->actingAs($this->user)->postJson(
            "/api/supplier/suppliers/{$this->supplier->id}/products",
            [
                'product_variant_id' => $this->variant->id,
                'default_unit_cost' => 8000,
            ]
        );

        $response->assertStatus(201);
        $response->assertJsonPath('data.product_variant_id', $this->variant->id);
        $response->assertJsonPath('data.default_unit_cost', 8000);

        $this->assertDatabaseHas('supplier_products', [
            'supplier_id' => $this->supplier->id,
            'product_variant_id' => $this->variant->id,
            'default_unit_cost' => 8000,
        ]);
    }

    public function test_can_unlink_product(): void
    {
        // First link the product
        SupplierProductModel::create([
            'supplier_id' => $this->supplier->id,
            'product_variant_id' => $this->variant->id,
            'default_unit_cost' => 8000,
        ]);

        $response = $this->actingAs($this->user)->deleteJson(
            "/api/supplier/suppliers/{$this->supplier->id}/products/{$this->variant->id}"
        );

        $response->assertStatus(204);

        $this->assertDatabaseMissing('supplier_products', [
            'supplier_id' => $this->supplier->id,
            'product_variant_id' => $this->variant->id,
        ]);
    }

    public function test_rejects_duplicate_link(): void
    {
        // Link the product first
        SupplierProductModel::create([
            'supplier_id' => $this->supplier->id,
            'product_variant_id' => $this->variant->id,
            'default_unit_cost' => 8000,
        ]);

        // Try to link again
        $response = $this->actingAs($this->user)->postJson(
            "/api/supplier/suppliers/{$this->supplier->id}/products",
            [
                'product_variant_id' => $this->variant->id,
                'default_unit_cost' => 9000,
            ]
        );

        $response->assertStatus(422);
    }

    public function test_default_cost_lookup(): void
    {
        // Link product with cost
        $this->actingAs($this->user)->postJson(
            "/api/supplier/suppliers/{$this->supplier->id}/products",
            [
                'product_variant_id' => $this->variant->id,
                'default_unit_cost' => 7500,
            ]
        )->assertStatus(201);

        // Retrieve linked products and verify cost is accessible
        $response = $this->actingAs($this->user)->getJson(
            "/api/supplier/suppliers/{$this->supplier->id}/products"
        );

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->variant->id, $data[0]['product_variant_id']);
        $this->assertEquals('7500.00', $data[0]['default_unit_cost']);
    }

    public function test_unlink_preserves_po_history(): void
    {
        // Link product
        SupplierProductModel::create([
            'supplier_id' => $this->supplier->id,
            'product_variant_id' => $this->variant->id,
            'default_unit_cost' => 8000,
        ]);

        // Create a confirmed PO that uses this product
        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-001',
            'order_date' => '2026-08-01',
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'total_amount' => 80000,
        ]);

        $poItem = PurchaseOrderItemModel::create([
            'purchase_order_id' => $po->id,
            'product_variant_id' => $this->variant->id,
            'product_name' => 'Test Product',
            'variant_name' => 'Default',
            'quantity' => 10,
            'unit_cost' => 8000,
            'subtotal' => 80000,
            'received_quantity' => 0,
        ]);

        // Unlink the product
        $response = $this->actingAs($this->user)->deleteJson(
            "/api/supplier/suppliers/{$this->supplier->id}/products/{$this->variant->id}"
        );

        $response->assertStatus(204);

        // Verify supplier_products is removed
        $this->assertDatabaseMissing('supplier_products', [
            'supplier_id' => $this->supplier->id,
            'product_variant_id' => $this->variant->id,
        ]);

        // Verify PO item is still intact
        $this->assertDatabaseHas('supplier_purchase_order_items', [
            'id' => $poItem->id,
            'purchase_order_id' => $po->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 10,
            'unit_cost' => 8000,
            'subtotal' => 80000,
        ]);

        // Verify PO itself is still intact
        $this->assertDatabaseHas('supplier_purchase_orders', [
            'id' => $po->id,
            'total_amount' => 80000,
            'status' => 'confirmed',
        ]);
    }
}
