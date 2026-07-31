<?php

namespace Tests\Property\Supplier;

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

/**
 * Property tests for Supplier-Product linkage (Properties 20-22).
 */
class SupplierProductPropertiesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private OutletModel $outlet;
    private SupplierModel $supplier1;
    private SupplierModel $supplier2;
    private ProductVariantModel $variant1;
    private ProductVariantModel $variant2;
    private ProductVariantModel $variant3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->outlet = OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Test Outlet',
            'business_type' => 'retail',
        ]);

        $this->supplier1 = SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Supplier One',
        ]);
        $this->supplier2 = SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Supplier Two',
        ]);

        $category = CategoryModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Test Category',
            'sort_order' => 0,
        ]);

        $product1 = ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $category->id,
            'name' => 'Product A',
            'base_price' => 10000,
            'status' => 'active',
            'track_stock' => true,
        ]);
        $this->variant1 = ProductVariantModel::create([
            'product_id' => $product1->id,
            'name' => 'Default',
            'price' => 10000,
            'stock_quantity' => 50,
        ]);

        $product2 = ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $category->id,
            'name' => 'Product B',
            'base_price' => 20000,
            'status' => 'active',
            'track_stock' => true,
        ]);
        $this->variant2 = ProductVariantModel::create([
            'product_id' => $product2->id,
            'name' => 'Default',
            'price' => 20000,
            'stock_quantity' => 30,
        ]);

        $product3 = ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $category->id,
            'name' => 'Product C',
            'base_price' => 30000,
            'status' => 'active',
            'track_stock' => true,
        ]);
        $this->variant3 = ProductVariantModel::create([
            'product_id' => $product3->id,
            'name' => 'Default',
            'price' => 30000,
            'stock_quantity' => 20,
        ]);
    }

    // =========================================================================
    // Property 20: A product can link to multiple suppliers; a supplier can link to multiple products
    // Validates: Requirements 7.1, 7.4, 7.5
    // =========================================================================

    /**
     * Property 20: One product linked to multiple suppliers.
     *
     * **Validates: Requirements 7.1, 7.4, 7.5**
     */
    public function test_property20_product_links_to_multiple_suppliers(): void
    {
        // Link variant1 to supplier1
        $this->actingAs($this->user)->postJson(
            "/api/supplier/suppliers/{$this->supplier1->id}/products",
            [
                'product_variant_id' => $this->variant1->id,
                'default_unit_cost' => 7000,
            ]
        )->assertStatus(201);

        // Link same variant1 to supplier2
        $this->actingAs($this->user)->postJson(
            "/api/supplier/suppliers/{$this->supplier2->id}/products",
            [
                'product_variant_id' => $this->variant1->id,
                'default_unit_cost' => 7500,
            ]
        )->assertStatus(201);

        // Both links exist with independent costs
        $this->assertDatabaseHas('supplier_products', [
            'supplier_id' => $this->supplier1->id,
            'product_variant_id' => $this->variant1->id,
            'default_unit_cost' => 7000,
        ]);
        $this->assertDatabaseHas('supplier_products', [
            'supplier_id' => $this->supplier2->id,
            'product_variant_id' => $this->variant1->id,
            'default_unit_cost' => 7500,
        ]);
    }

    /**
     * Property 20: One supplier linked to multiple products.
     *
     * **Validates: Requirements 7.1, 7.4, 7.5**
     */
    public function test_property20_supplier_links_to_multiple_products(): void
    {
        $variants = [$this->variant1, $this->variant2, $this->variant3];
        $costs = [5000, 12000, 25000];

        foreach ($variants as $i => $variant) {
            $this->actingAs($this->user)->postJson(
                "/api/supplier/suppliers/{$this->supplier1->id}/products",
                [
                    'product_variant_id' => $variant->id,
                    'default_unit_cost' => $costs[$i],
                ]
            )->assertStatus(201);
        }

        // Verify all links exist
        $response = $this->actingAs($this->user)->getJson(
            "/api/supplier/suppliers/{$this->supplier1->id}/products"
        );
        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Property 20: Many-to-many relationship works independently.
     *
     * **Validates: Requirements 7.1, 7.4, 7.5**
     */
    public function test_property20_many_to_many_independence(): void
    {
        // supplier1 → variant1, variant2
        $this->actingAs($this->user)->postJson(
            "/api/supplier/suppliers/{$this->supplier1->id}/products",
            ['product_variant_id' => $this->variant1->id, 'default_unit_cost' => 5000]
        )->assertStatus(201);
        $this->actingAs($this->user)->postJson(
            "/api/supplier/suppliers/{$this->supplier1->id}/products",
            ['product_variant_id' => $this->variant2->id, 'default_unit_cost' => 10000]
        )->assertStatus(201);

        // supplier2 → variant2, variant3
        $this->actingAs($this->user)->postJson(
            "/api/supplier/suppliers/{$this->supplier2->id}/products",
            ['product_variant_id' => $this->variant2->id, 'default_unit_cost' => 11000]
        )->assertStatus(201);
        $this->actingAs($this->user)->postJson(
            "/api/supplier/suppliers/{$this->supplier2->id}/products",
            ['product_variant_id' => $this->variant3->id, 'default_unit_cost' => 22000]
        )->assertStatus(201);

        // supplier1 has 2 products
        $response1 = $this->actingAs($this->user)->getJson(
            "/api/supplier/suppliers/{$this->supplier1->id}/products"
        );
        $this->assertCount(2, $response1->json('data'));

        // supplier2 has 2 products
        $response2 = $this->actingAs($this->user)->getJson(
            "/api/supplier/suppliers/{$this->supplier2->id}/products"
        );
        $this->assertCount(2, $response2->json('data'));
    }

    // =========================================================================
    // Property 21: Linked product's default cost is retrievable and correct
    // Validates: Requirements 7.2
    // =========================================================================

    /**
     * Property 21: Default unit cost is stored and retrievable correctly.
     *
     * **Validates: Requirements 7.2**
     */
    public function test_property21_default_unit_cost_retrievable(): void
    {
        $costs = [
            ['variant' => $this->variant1, 'cost' => 6500],
            ['variant' => $this->variant2, 'cost' => 15000],
            ['variant' => $this->variant3, 'cost' => 28000],
        ];

        foreach ($costs as $data) {
            $this->actingAs($this->user)->postJson(
                "/api/supplier/suppliers/{$this->supplier1->id}/products",
                [
                    'product_variant_id' => $data['variant']->id,
                    'default_unit_cost' => $data['cost'],
                ]
            )->assertStatus(201);
        }

        // Retrieve and verify each cost
        $response = $this->actingAs($this->user)->getJson(
            "/api/supplier/suppliers/{$this->supplier1->id}/products"
        );
        $response->assertOk();

        $products = collect($response->json('data'));

        foreach ($costs as $data) {
            $linked = $products->firstWhere('product_variant_id', $data['variant']->id);
            $this->assertNotNull($linked);
            $this->assertEquals(
                number_format($data['cost'], 2, '.', ''),
                $linked['default_unit_cost'],
                "Default unit cost mismatch for variant {$data['variant']->id}"
            );
        }
    }

    /**
     * Property 21: Same product, different suppliers have different default costs.
     *
     * **Validates: Requirements 7.2**
     */
    public function test_property21_independent_costs_per_supplier(): void
    {
        $costSupplier1 = 8000;
        $costSupplier2 = 9500;

        $this->actingAs($this->user)->postJson(
            "/api/supplier/suppliers/{$this->supplier1->id}/products",
            ['product_variant_id' => $this->variant1->id, 'default_unit_cost' => $costSupplier1]
        )->assertStatus(201);

        $this->actingAs($this->user)->postJson(
            "/api/supplier/suppliers/{$this->supplier2->id}/products",
            ['product_variant_id' => $this->variant1->id, 'default_unit_cost' => $costSupplier2]
        )->assertStatus(201);

        // Supplier 1's cost
        $response1 = $this->actingAs($this->user)->getJson(
            "/api/supplier/suppliers/{$this->supplier1->id}/products"
        );
        $linked1 = collect($response1->json('data'))->firstWhere('product_variant_id', $this->variant1->id);
        $this->assertEquals(number_format($costSupplier1, 2, '.', ''), $linked1['default_unit_cost']);

        // Supplier 2's cost
        $response2 = $this->actingAs($this->user)->getJson(
            "/api/supplier/suppliers/{$this->supplier2->id}/products"
        );
        $linked2 = collect($response2->json('data'))->firstWhere('product_variant_id', $this->variant1->id);
        $this->assertEquals(number_format($costSupplier2, 2, '.', ''), $linked2['default_unit_cost']);
    }

    // =========================================================================
    // Property 22: Unlinking a product doesn't affect existing PO items
    // Validates: Requirements 7.6
    // =========================================================================

    /**
     * Property 22: Unlink preserves all PO item data.
     *
     * **Validates: Requirements 7.6**
     */
    public function test_property22_unlink_preserves_po_history(): void
    {
        // Link product
        SupplierProductModel::create([
            'supplier_id' => $this->supplier1->id,
            'product_variant_id' => $this->variant1->id,
            'default_unit_cost' => 8000,
        ]);

        // Create PO with line items using this product
        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier1->id,
            'po_number' => 'PO-20260801-001',
            'order_date' => '2026-08-01',
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'total_amount' => 80000,
        ]);

        $poItem = PurchaseOrderItemModel::create([
            'purchase_order_id' => $po->id,
            'product_variant_id' => $this->variant1->id,
            'product_name' => 'Product A',
            'variant_name' => 'Default',
            'quantity' => 10,
            'unit_cost' => 8000,
            'subtotal' => 80000,
            'received_quantity' => 5,
        ]);

        // Unlink
        $this->actingAs($this->user)->deleteJson(
            "/api/supplier/suppliers/{$this->supplier1->id}/products/{$this->variant1->id}"
        )->assertStatus(204);

        // Link is removed
        $this->assertDatabaseMissing('supplier_products', [
            'supplier_id' => $this->supplier1->id,
            'product_variant_id' => $this->variant1->id,
        ]);

        // PO item data is fully intact
        $this->assertDatabaseHas('supplier_purchase_order_items', [
            'id' => $poItem->id,
            'purchase_order_id' => $po->id,
            'product_variant_id' => $this->variant1->id,
            'product_name' => 'Product A',
            'variant_name' => 'Default',
            'quantity' => 10,
            'unit_cost' => 8000,
            'subtotal' => 80000,
            'received_quantity' => 5,
        ]);

        // PO itself is intact
        $this->assertDatabaseHas('supplier_purchase_orders', [
            'id' => $po->id,
            'total_amount' => 80000,
            'status' => 'confirmed',
        ]);
    }

    /**
     * Property 22: Multiple PO items preserved after unlink.
     *
     * **Validates: Requirements 7.6**
     */
    public function test_property22_multiple_pos_preserved_after_unlink(): void
    {
        SupplierProductModel::create([
            'supplier_id' => $this->supplier1->id,
            'product_variant_id' => $this->variant1->id,
            'default_unit_cost' => 5000,
        ]);

        // Create multiple POs referencing this product
        $poIds = [];
        for ($i = 1; $i <= 3; $i++) {
            $po = PurchaseOrderModel::create([
                'outlet_id' => $this->outlet->id,
                'supplier_id' => $this->supplier1->id,
                'po_number' => "PO-20260801-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'order_date' => '2026-08-01',
                'status' => $i === 3 ? 'received' : 'confirmed',
                'payment_status' => 'unpaid',
                'total_amount' => $i * 50000,
            ]);

            PurchaseOrderItemModel::create([
                'purchase_order_id' => $po->id,
                'product_variant_id' => $this->variant1->id,
                'product_name' => 'Product A',
                'variant_name' => 'Default',
                'quantity' => $i * 10,
                'unit_cost' => 5000,
                'subtotal' => $i * 50000,
                'received_quantity' => $i === 3 ? $i * 10 : 0,
            ]);

            $poIds[] = $po->id;
        }

        // Unlink
        $this->actingAs($this->user)->deleteJson(
            "/api/supplier/suppliers/{$this->supplier1->id}/products/{$this->variant1->id}"
        )->assertStatus(204);

        // All PO items still exist
        foreach ($poIds as $poId) {
            $this->assertDatabaseHas('supplier_purchase_order_items', [
                'purchase_order_id' => $poId,
                'product_variant_id' => $this->variant1->id,
            ]);
        }
    }
}
