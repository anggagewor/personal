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
use Modules\User\Infrastructure\Models\UserModel as User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Property tests for Goods Receiving (Properties 13-16).
 */
class GoodsReceiptPropertiesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private OutletModel $outlet;
    private SupplierModel $supplier;
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
            'stock_quantity' => 100,
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
            'stock_quantity' => 50,
        ]);

        $product3 = ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $category->id,
            'name' => 'Product 3',
            'base_price' => 30000,
            'status' => 'active',
            'track_stock' => true,
        ]);
        $this->variant3 = ProductVariantModel::create([
            'product_id' => $product3->id,
            'name' => 'Default',
            'price' => 30000,
            'stock_quantity' => 25,
        ]);
    }

    private function createConfirmedPO(array $items): PurchaseOrderModel
    {
        static $seq = 0;
        $seq++;

        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-' . str_pad($seq, 3, '0', STR_PAD_LEFT),
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

    // =========================================================================
    // Property 13: Each goods receipt creates stock adjustments that increment stock
    // Validates: Requirements 4.2
    // =========================================================================

    /**
     * Property 13: Goods receipt increments stock by received quantity.
     *
     * **Validates: Requirements 4.2**
     */
    public function test_property13_stock_incremented_on_receipt(): void
    {
        $initialStock1 = $this->variant1->stock_quantity;
        $initialStock2 = $this->variant2->stock_quantity;

        $po = $this->createConfirmedPO([
            [
                'product_variant_id' => $this->variant1->id,
                'product_name' => 'Product 1',
                'quantity' => 15,
                'unit_cost' => 8000,
            ],
            [
                'product_variant_id' => $this->variant2->id,
                'product_name' => 'Product 2',
                'quantity' => 8,
                'unit_cost' => 15000,
            ],
        ]);

        $poItems = $po->items;
        $receiveQty1 = 10;
        $receiveQty2 = 5;

        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/receipts",
            [
                'receipt_date' => now()->toDateString(),
                'items' => [
                    [
                        'purchase_order_item_id' => $poItems[0]->id,
                        'product_variant_id' => $this->variant1->id,
                        'quantity' => $receiveQty1,
                    ],
                    [
                        'purchase_order_item_id' => $poItems[1]->id,
                        'product_variant_id' => $this->variant2->id,
                        'quantity' => $receiveQty2,
                    ],
                ],
            ]
        )->assertStatus(201);

        // Stock should increase by received quantities
        $this->variant1->refresh();
        $this->variant2->refresh();

        $this->assertEquals($initialStock1 + $receiveQty1, $this->variant1->stock_quantity);
        $this->assertEquals($initialStock2 + $receiveQty2, $this->variant2->stock_quantity);

        // Stock adjustments exist
        $this->assertDatabaseHas('pos_stock_adjustments', [
            'product_variant_id' => $this->variant1->id,
            'type' => 'restock',
            'quantity' => $receiveQty1,
        ]);
        $this->assertDatabaseHas('pos_stock_adjustments', [
            'product_variant_id' => $this->variant2->id,
            'type' => 'restock',
            'quantity' => $receiveQty2,
        ]);
    }

    /**
     * Property 13: Multiple receipts each create independent stock adjustments.
     *
     * **Validates: Requirements 4.2**
     */
    public function test_property13_multiple_receipts_cumulative_stock(): void
    {
        $initialStock = $this->variant1->stock_quantity;

        $po = $this->createConfirmedPO([
            [
                'product_variant_id' => $this->variant1->id,
                'product_name' => 'Product 1',
                'quantity' => 20,
                'unit_cost' => 5000,
            ],
        ]);

        $poItems = $po->items;

        // First receipt
        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/receipts",
            [
                'receipt_date' => now()->toDateString(),
                'items' => [
                    [
                        'purchase_order_item_id' => $poItems[0]->id,
                        'product_variant_id' => $this->variant1->id,
                        'quantity' => 8,
                    ],
                ],
            ]
        )->assertStatus(201);

        // Second receipt
        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/receipts",
            [
                'receipt_date' => now()->toDateString(),
                'items' => [
                    [
                        'purchase_order_item_id' => $poItems[0]->id,
                        'product_variant_id' => $this->variant1->id,
                        'quantity' => 7,
                    ],
                ],
            ]
        )->assertStatus(201);

        $this->variant1->refresh();
        $this->assertEquals($initialStock + 8 + 7, $this->variant1->stock_quantity);
    }

    // =========================================================================
    // Property 14: Received qty can never exceed ordered qty minus already received
    // Validates: Requirements 4.8
    // =========================================================================

    /**
     * Property 14: Over-delivery is rejected across various scenarios.
     *
     * **Validates: Requirements 4.8**
     */
    public function test_property14_over_delivery_rejected(): void
    {
        $po = $this->createConfirmedPO([
            [
                'product_variant_id' => $this->variant1->id,
                'product_name' => 'Product 1',
                'quantity' => 10,
                'unit_cost' => 5000,
            ],
        ]);

        $poItems = $po->items;

        // Try to receive more than ordered
        $response = $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/receipts",
            [
                'receipt_date' => now()->toDateString(),
                'items' => [
                    [
                        'purchase_order_item_id' => $poItems[0]->id,
                        'product_variant_id' => $this->variant1->id,
                        'quantity' => 11,
                    ],
                ],
            ]
        );

        $response->assertStatus(422);
    }

    /**
     * Property 14: Over-delivery rejected after partial receipt.
     *
     * **Validates: Requirements 4.8**
     */
    public function test_property14_over_delivery_after_partial_receipt(): void
    {
        $po = $this->createConfirmedPO([
            [
                'product_variant_id' => $this->variant1->id,
                'product_name' => 'Product 1',
                'quantity' => 10,
                'unit_cost' => 5000,
            ],
        ]);

        $poItems = $po->items;

        // First receipt: 7 of 10
        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/receipts",
            [
                'receipt_date' => now()->toDateString(),
                'items' => [
                    [
                        'purchase_order_item_id' => $poItems[0]->id,
                        'product_variant_id' => $this->variant1->id,
                        'quantity' => 7,
                    ],
                ],
            ]
        )->assertStatus(201);

        // Try to receive 4 more (remaining = 3)
        $response = $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/receipts",
            [
                'receipt_date' => now()->toDateString(),
                'items' => [
                    [
                        'purchase_order_item_id' => $poItems[0]->id,
                        'product_variant_id' => $this->variant1->id,
                        'quantity' => 4,
                    ],
                ],
            ]
        );

        $response->assertStatus(422);
    }

    /**
     * Property 14: Exact remaining quantity is allowed (boundary).
     *
     * **Validates: Requirements 4.8**
     */
    public function test_property14_exact_remaining_allowed(): void
    {
        $po = $this->createConfirmedPO([
            [
                'product_variant_id' => $this->variant1->id,
                'product_name' => 'Product 1',
                'quantity' => 10,
                'unit_cost' => 5000,
            ],
        ]);

        $poItems = $po->items;

        // First receipt: 6 of 10
        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/receipts",
            [
                'receipt_date' => now()->toDateString(),
                'items' => [
                    [
                        'purchase_order_item_id' => $poItems[0]->id,
                        'product_variant_id' => $this->variant1->id,
                        'quantity' => 6,
                    ],
                ],
            ]
        )->assertStatus(201);

        // Second receipt: exactly 4 remaining
        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/receipts",
            [
                'receipt_date' => now()->toDateString(),
                'items' => [
                    [
                        'purchase_order_item_id' => $poItems[0]->id,
                        'product_variant_id' => $this->variant1->id,
                        'quantity' => 4,
                    ],
                ],
            ]
        )->assertStatus(201);
    }

    // =========================================================================
    // Property 15: If all items fully received → "received", otherwise → "partial"
    // Validates: Requirements 4.4, 4.5
    // =========================================================================

    /**
     * Property 15: Full receipt → status "received".
     *
     * **Validates: Requirements 4.4, 4.5**
     */
    public function test_property15_all_received_status_received(): void
    {
        $po = $this->createConfirmedPO([
            [
                'product_variant_id' => $this->variant1->id,
                'product_name' => 'Product 1',
                'quantity' => 5,
                'unit_cost' => 10000,
            ],
            [
                'product_variant_id' => $this->variant2->id,
                'product_name' => 'Product 2',
                'quantity' => 3,
                'unit_cost' => 20000,
            ],
        ]);

        $poItems = $po->items;

        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/receipts",
            [
                'receipt_date' => now()->toDateString(),
                'items' => [
                    [
                        'purchase_order_item_id' => $poItems[0]->id,
                        'product_variant_id' => $this->variant1->id,
                        'quantity' => 5,
                    ],
                    [
                        'purchase_order_item_id' => $poItems[1]->id,
                        'product_variant_id' => $this->variant2->id,
                        'quantity' => 3,
                    ],
                ],
            ]
        )->assertStatus(201);

        $po->refresh();
        $this->assertEquals('received', $po->status);
    }

    /**
     * Property 15: Partial receipt → status "partial".
     *
     * **Validates: Requirements 4.4, 4.5**
     */
    public function test_property15_partial_receipt_status_partial(): void
    {
        $po = $this->createConfirmedPO([
            [
                'product_variant_id' => $this->variant1->id,
                'product_name' => 'Product 1',
                'quantity' => 10,
                'unit_cost' => 10000,
            ],
            [
                'product_variant_id' => $this->variant2->id,
                'product_name' => 'Product 2',
                'quantity' => 8,
                'unit_cost' => 20000,
            ],
        ]);

        $poItems = $po->items;

        // Receive only first item partially
        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/receipts",
            [
                'receipt_date' => now()->toDateString(),
                'items' => [
                    [
                        'purchase_order_item_id' => $poItems[0]->id,
                        'product_variant_id' => $this->variant1->id,
                        'quantity' => 4,
                    ],
                ],
            ]
        )->assertStatus(201);

        $po->refresh();
        $this->assertEquals('partial', $po->status);
    }

    /**
     * Property 15: Split delivery transitions partial → received.
     *
     * **Validates: Requirements 4.4, 4.5**
     */
    public function test_property15_split_delivery_transition(): void
    {
        $po = $this->createConfirmedPO([
            [
                'product_variant_id' => $this->variant1->id,
                'product_name' => 'Product 1',
                'quantity' => 6,
                'unit_cost' => 10000,
            ],
        ]);

        $poItems = $po->items;

        // First receipt: partial
        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/receipts",
            [
                'receipt_date' => now()->toDateString(),
                'items' => [
                    [
                        'purchase_order_item_id' => $poItems[0]->id,
                        'product_variant_id' => $this->variant1->id,
                        'quantity' => 3,
                    ],
                ],
            ]
        )->assertStatus(201);

        $po->refresh();
        $this->assertEquals('partial', $po->status);

        // Second receipt: complete the remaining
        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/receipts",
            [
                'receipt_date' => now()->toDateString(),
                'items' => [
                    [
                        'purchase_order_item_id' => $poItems[0]->id,
                        'product_variant_id' => $this->variant1->id,
                        'quantity' => 3,
                    ],
                ],
            ]
        )->assertStatus(201);

        $po->refresh();
        $this->assertEquals('received', $po->status);
    }

    // =========================================================================
    // Property 16: Only confirmed/partial POs accept goods receipts
    // Validates: Requirements 4.9
    // =========================================================================

    public static function nonReceivableStatusProvider(): array
    {
        return [
            'draft' => ['draft'],
            'cancelled' => ['cancelled'],
        ];
    }

    /**
     * Property 16: Draft/cancelled POs reject goods receipts.
     *
     * **Validates: Requirements 4.9**
     */
    #[DataProvider('nonReceivableStatusProvider')]
    public function test_property16_non_receivable_states_reject(string $status): void
    {
        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-099',
            'order_date' => now()->toDateString(),
            'status' => $status,
            'payment_status' => 'unpaid',
            'total_amount' => 50000,
            'cancelled_at' => $status === 'cancelled' ? now() : null,
        ]);

        $poItem = PurchaseOrderItemModel::create([
            'purchase_order_id' => $po->id,
            'product_variant_id' => $this->variant1->id,
            'product_name' => 'Product 1',
            'variant_name' => 'Default',
            'quantity' => 5,
            'unit_cost' => 10000,
            'subtotal' => 50000,
            'received_quantity' => 0,
        ]);

        $response = $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/receipts",
            [
                'receipt_date' => now()->toDateString(),
                'items' => [
                    [
                        'purchase_order_item_id' => $poItem->id,
                        'product_variant_id' => $this->variant1->id,
                        'quantity' => 2,
                    ],
                ],
            ]
        );

        $response->assertStatus(422);
    }

    /**
     * Property 16: Confirmed and partial POs accept goods receipts.
     *
     * **Validates: Requirements 4.9**
     */
    public function test_property16_confirmed_and_partial_accept_receipts(): void
    {
        // Test confirmed
        $po1 = $this->createConfirmedPO([
            [
                'product_variant_id' => $this->variant1->id,
                'product_name' => 'Product 1',
                'quantity' => 10,
                'unit_cost' => 5000,
            ],
        ]);

        $poItems1 = $po1->items;
        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po1->id}/receipts",
            [
                'receipt_date' => now()->toDateString(),
                'items' => [
                    [
                        'purchase_order_item_id' => $poItems1[0]->id,
                        'product_variant_id' => $this->variant1->id,
                        'quantity' => 3,
                    ],
                ],
            ]
        )->assertStatus(201);

        // PO is now partial, second receipt should also work
        $po1->refresh();
        $this->assertEquals('partial', $po1->status);

        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po1->id}/receipts",
            [
                'receipt_date' => now()->toDateString(),
                'items' => [
                    [
                        'purchase_order_item_id' => $poItems1[0]->id,
                        'product_variant_id' => $this->variant1->id,
                        'quantity' => 2,
                    ],
                ],
            ]
        )->assertStatus(201);
    }
}
