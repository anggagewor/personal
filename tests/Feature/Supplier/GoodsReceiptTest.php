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
use Modules\User\Infrastructure\Models\UserModel as User;
use Tests\TestCase;

class GoodsReceiptTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private OutletModel $outlet;
    private SupplierModel $supplier;
    private ProductVariantModel $variant1;
    private ProductVariantModel $variant2;

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
            'name' => 'Supplier A',
        ]);

        $category = CategoryModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Test Category',
            'sort_order' => 0,
        ]);
        $product1 = ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $category->id,
            'name' => 'Product 1',
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
            'name' => 'Product 2',
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
    }

    private function createConfirmedPO(array $items = []): PurchaseOrderModel
    {
        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-' . date('Ymd') . '-001',
            'order_date' => now()->toDateString(),
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'total_amount' => 0,
        ]);

        $total = 0;
        foreach ($items as $item) {
            $subtotal = $item['quantity'] * $item['unit_cost'];
            PurchaseOrderItemModel::create([
                'purchase_order_id' => $po->id,
                'product_variant_id' => $item['product_variant_id'],
                'product_name' => $item['product_name'],
                'variant_name' => $item['variant_name'] ?? 'Default',
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
                'subtotal' => $subtotal,
                'received_quantity' => $item['received_quantity'] ?? 0,
            ]);
            $total += $subtotal;
        }

        $po->update(['total_amount' => $total]);

        return $po->fresh();
    }

    public function test_can_create_full_receipt(): void
    {
        $po = $this->createConfirmedPO([
            [
                'product_variant_id' => $this->variant1->id,
                'product_name' => 'Product 1',
                'quantity' => 10,
                'unit_cost' => 8000,
            ],
            [
                'product_variant_id' => $this->variant2->id,
                'product_name' => 'Product 2',
                'quantity' => 5,
                'unit_cost' => 15000,
            ],
        ]);

        $poItems = $po->items;

        $response = $this->actingAs($this->user)->postJson("/api/supplier/purchase-orders/{$po->id}/receipts", [
            'receipt_date' => now()->toDateString(),
            'notes' => 'Barang lengkap diterima',
            'items' => [
                [
                    'purchase_order_item_id' => $poItems[0]->id,
                    'product_variant_id' => $this->variant1->id,
                    'quantity' => 10,
                ],
                [
                    'purchase_order_item_id' => $poItems[1]->id,
                    'product_variant_id' => $this->variant2->id,
                    'quantity' => 5,
                ],
            ],
        ]);

        $response->assertStatus(201);

        // PO status should be "received"
        $po->refresh();
        $this->assertEquals('received', $po->status);

        // Goods receipt record exists
        $this->assertDatabaseHas('supplier_goods_receipts', [
            'purchase_order_id' => $po->id,
            'notes' => 'Barang lengkap diterima',
        ]);
    }

    public function test_can_create_partial_receipt(): void
    {
        $po = $this->createConfirmedPO([
            [
                'product_variant_id' => $this->variant1->id,
                'product_name' => 'Product 1',
                'quantity' => 10,
                'unit_cost' => 8000,
            ],
            [
                'product_variant_id' => $this->variant2->id,
                'product_name' => 'Product 2',
                'quantity' => 5,
                'unit_cost' => 15000,
            ],
        ]);

        $poItems = $po->items;

        // Receive only part of item 1 and none of item 2
        $response = $this->actingAs($this->user)->postJson("/api/supplier/purchase-orders/{$po->id}/receipts", [
            'receipt_date' => now()->toDateString(),
            'notes' => 'Pengiriman pertama',
            'items' => [
                [
                    'purchase_order_item_id' => $poItems[0]->id,
                    'product_variant_id' => $this->variant1->id,
                    'quantity' => 6,
                ],
            ],
        ]);

        $response->assertStatus(201);

        // PO status should be "partial"
        $po->refresh();
        $this->assertEquals('partial', $po->status);
    }

    public function test_multiple_receipts_split_delivery(): void
    {
        $po = $this->createConfirmedPO([
            [
                'product_variant_id' => $this->variant1->id,
                'product_name' => 'Product 1',
                'quantity' => 10,
                'unit_cost' => 8000,
            ],
            [
                'product_variant_id' => $this->variant2->id,
                'product_name' => 'Product 2',
                'quantity' => 5,
                'unit_cost' => 15000,
            ],
        ]);

        $poItems = $po->items;

        // First receipt: partial delivery of item 1
        $this->actingAs($this->user)->postJson("/api/supplier/purchase-orders/{$po->id}/receipts", [
            'receipt_date' => now()->toDateString(),
            'notes' => 'Pengiriman pertama',
            'items' => [
                [
                    'purchase_order_item_id' => $poItems[0]->id,
                    'product_variant_id' => $this->variant1->id,
                    'quantity' => 6,
                ],
            ],
        ])->assertStatus(201);

        $po->refresh();
        $this->assertEquals('partial', $po->status);

        // Second receipt: remaining item 1 + all item 2
        $this->actingAs($this->user)->postJson("/api/supplier/purchase-orders/{$po->id}/receipts", [
            'receipt_date' => now()->toDateString(),
            'notes' => 'Pengiriman kedua',
            'items' => [
                [
                    'purchase_order_item_id' => $poItems[0]->id,
                    'product_variant_id' => $this->variant1->id,
                    'quantity' => 4,
                ],
                [
                    'purchase_order_item_id' => $poItems[1]->id,
                    'product_variant_id' => $this->variant2->id,
                    'quantity' => 5,
                ],
            ],
        ])->assertStatus(201);

        // PO status should now be "received"
        $po->refresh();
        $this->assertEquals('received', $po->status);

        // Two receipts exist for this PO
        $this->assertDatabaseCount('supplier_goods_receipts', 2);
    }

    public function test_rejects_over_delivery(): void
    {
        $po = $this->createConfirmedPO([
            [
                'product_variant_id' => $this->variant1->id,
                'product_name' => 'Product 1',
                'quantity' => 10,
                'unit_cost' => 8000,
            ],
        ]);

        $poItems = $po->items;

        // Try to receive more than ordered
        $response = $this->actingAs($this->user)->postJson("/api/supplier/purchase-orders/{$po->id}/receipts", [
            'receipt_date' => now()->toDateString(),
            'items' => [
                [
                    'purchase_order_item_id' => $poItems[0]->id,
                    'product_variant_id' => $this->variant1->id,
                    'quantity' => 15,
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_receive_on_draft_po(): void
    {
        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-' . date('Ymd') . '-002',
            'order_date' => now()->toDateString(),
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'total_amount' => 80000,
        ]);

        $poItem = PurchaseOrderItemModel::create([
            'purchase_order_id' => $po->id,
            'product_variant_id' => $this->variant1->id,
            'product_name' => 'Product 1',
            'variant_name' => 'Default',
            'quantity' => 10,
            'unit_cost' => 8000,
            'subtotal' => 80000,
            'received_quantity' => 0,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/supplier/purchase-orders/{$po->id}/receipts", [
            'receipt_date' => now()->toDateString(),
            'items' => [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'product_variant_id' => $this->variant1->id,
                    'quantity' => 5,
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_receive_on_cancelled_po(): void
    {
        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-' . date('Ymd') . '-003',
            'order_date' => now()->toDateString(),
            'status' => 'cancelled',
            'payment_status' => 'unpaid',
            'total_amount' => 80000,
            'cancelled_at' => now(),
        ]);

        $poItem = PurchaseOrderItemModel::create([
            'purchase_order_id' => $po->id,
            'product_variant_id' => $this->variant1->id,
            'product_name' => 'Product 1',
            'variant_name' => 'Default',
            'quantity' => 10,
            'unit_cost' => 8000,
            'subtotal' => 80000,
            'received_quantity' => 0,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/supplier/purchase-orders/{$po->id}/receipts", [
            'receipt_date' => now()->toDateString(),
            'items' => [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'product_variant_id' => $this->variant1->id,
                    'quantity' => 5,
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_stock_adjustment_created(): void
    {
        $po = $this->createConfirmedPO([
            [
                'product_variant_id' => $this->variant1->id,
                'product_name' => 'Product 1',
                'quantity' => 10,
                'unit_cost' => 8000,
            ],
        ]);

        $poItems = $po->items;

        $this->actingAs($this->user)->postJson("/api/supplier/purchase-orders/{$po->id}/receipts", [
            'receipt_date' => now()->toDateString(),
            'items' => [
                [
                    'purchase_order_item_id' => $poItems[0]->id,
                    'product_variant_id' => $this->variant1->id,
                    'quantity' => 7,
                ],
            ],
        ])->assertStatus(201);

        // Stock adjustment with type "restock" should be created
        $this->assertDatabaseHas('pos_stock_adjustments', [
            'product_variant_id' => $this->variant1->id,
            'type' => 'restock',
            'quantity' => 7,
        ]);

        // Stock quantity should be incremented
        $this->variant1->refresh();
        $this->assertEquals(57, $this->variant1->stock_quantity);
    }

    public function test_received_quantity_updated_on_po_items(): void
    {
        $po = $this->createConfirmedPO([
            [
                'product_variant_id' => $this->variant1->id,
                'product_name' => 'Product 1',
                'quantity' => 10,
                'unit_cost' => 8000,
            ],
            [
                'product_variant_id' => $this->variant2->id,
                'product_name' => 'Product 2',
                'quantity' => 5,
                'unit_cost' => 15000,
            ],
        ]);

        $poItems = $po->items;

        // First receipt: partial
        $this->actingAs($this->user)->postJson("/api/supplier/purchase-orders/{$po->id}/receipts", [
            'receipt_date' => now()->toDateString(),
            'items' => [
                [
                    'purchase_order_item_id' => $poItems[0]->id,
                    'product_variant_id' => $this->variant1->id,
                    'quantity' => 4,
                ],
                [
                    'purchase_order_item_id' => $poItems[1]->id,
                    'product_variant_id' => $this->variant2->id,
                    'quantity' => 2,
                ],
            ],
        ])->assertStatus(201);

        // Verify received_quantity updated
        $this->assertDatabaseHas('supplier_purchase_order_items', [
            'id' => $poItems[0]->id,
            'received_quantity' => 4,
        ]);
        $this->assertDatabaseHas('supplier_purchase_order_items', [
            'id' => $poItems[1]->id,
            'received_quantity' => 2,
        ]);

        // Second receipt: more items
        $this->actingAs($this->user)->postJson("/api/supplier/purchase-orders/{$po->id}/receipts", [
            'receipt_date' => now()->toDateString(),
            'items' => [
                [
                    'purchase_order_item_id' => $poItems[0]->id,
                    'product_variant_id' => $this->variant1->id,
                    'quantity' => 3,
                ],
            ],
        ])->assertStatus(201);

        // Verify received_quantity is cumulative
        $this->assertDatabaseHas('supplier_purchase_order_items', [
            'id' => $poItems[0]->id,
            'received_quantity' => 7,
        ]);
    }
}
