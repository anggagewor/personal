<?php

namespace Tests\Property\Supplier;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderItemModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderModel;
use Modules\Supplier\Infrastructure\Models\SupplierModel;
use Modules\Supplier\Infrastructure\Models\SupplierPaymentModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Property tests for Purchase Order (Properties 6-12).
 */
class PurchaseOrderPropertiesTest extends TestCase
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

    // =========================================================================
    // Property 6: PO number format and uniqueness
    // Validates: Requirements 2.2
    // =========================================================================

    /**
     * Property 6: Every generated PO number matches PO-{YYYYMMDD}-{SEQ} format and is unique.
     *
     * **Validates: Requirements 2.2**
     */
    public function test_property6_po_number_format(): void
    {
        $poNumbers = [];

        for ($i = 0; $i < 5; $i++) {
            $response = $this->actingAs($this->user)->postJson(
                "/api/supplier/outlets/{$this->outlet->id}/purchase-orders",
                [
                    'supplier_id' => $this->supplier->id,
                    'order_date' => '2026-08-01',
                    'items' => [
                        [
                            'product_variant_id' => 1,
                            'product_name' => "Product {$i}",
                            'variant_name' => 'Default',
                            'quantity' => $i + 1,
                            'unit_cost' => 10000,
                        ],
                    ],
                ]
            );

            $response->assertStatus(201);
            $poNumber = $response->json('data.po_number');

            // Verify format: PO-YYYYMMDD-SEQ (3 digits)
            $this->assertMatchesRegularExpression(
                '/^PO-\d{8}-\d{3}$/',
                $poNumber,
                "PO number '{$poNumber}' does not match required format"
            );

            $poNumbers[] = $poNumber;
        }

        // All PO numbers are unique
        $this->assertCount(5, array_unique($poNumbers), 'PO numbers are not all unique');
    }

    /**
     * Property 6: PO numbers sequential within same date.
     *
     * **Validates: Requirements 2.2**
     */
    public function test_property6_po_number_sequential(): void
    {
        $sequences = [];

        for ($i = 0; $i < 3; $i++) {
            $response = $this->actingAs($this->user)->postJson(
                "/api/supplier/outlets/{$this->outlet->id}/purchase-orders",
                [
                    'supplier_id' => $this->supplier->id,
                    'order_date' => '2026-09-15',
                    'items' => [
                        [
                            'product_variant_id' => 1,
                            'product_name' => 'Product',
                            'variant_name' => 'Default',
                            'quantity' => 1,
                            'unit_cost' => 5000,
                        ],
                    ],
                ]
            );

            $response->assertStatus(201);
            $poNumber = $response->json('data.po_number');
            // Extract sequence
            $parts = explode('-', $poNumber);
            $sequences[] = (int) end($parts);
        }

        // Sequences should be monotonically increasing
        for ($i = 1; $i < count($sequences); $i++) {
            $this->assertGreaterThan(
                $sequences[$i - 1],
                $sequences[$i],
                'PO sequence numbers are not monotonically increasing'
            );
        }
    }

    // =========================================================================
    // Property 7: PO total = sum(qty × unit_cost) for all items
    // Validates: Requirements 2.4
    // =========================================================================

    public static function poItemsProvider(): array
    {
        return [
            'single item' => [[
                ['quantity' => 10, 'unit_cost' => 50000],
            ]],
            'multiple items' => [[
                ['quantity' => 5, 'unit_cost' => 30000],
                ['quantity' => 3, 'unit_cost' => 75000],
                ['quantity' => 20, 'unit_cost' => 12000],
            ]],
            'large quantities' => [[
                ['quantity' => 100, 'unit_cost' => 5000],
                ['quantity' => 200, 'unit_cost' => 2500],
            ]],
            'decimal unit costs' => [[
                ['quantity' => 7, 'unit_cost' => 15500],
                ['quantity' => 3, 'unit_cost' => 8750],
            ]],
        ];
    }

    /**
     * Property 7: PO total always equals sum of (qty × unit_cost) for all items.
     *
     * **Validates: Requirements 2.4**
     */
    #[DataProvider('poItemsProvider')]
    public function test_property7_po_total_calculation(array $items): void
    {
        $expectedTotal = 0;
        $apiItems = [];

        foreach ($items as $i => $item) {
            $expectedTotal += $item['quantity'] * $item['unit_cost'];
            $apiItems[] = [
                'product_variant_id' => $i + 1,
                'product_name' => "Product {$i}",
                'variant_name' => 'Default',
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
            ];
        }

        $response = $this->actingAs($this->user)->postJson(
            "/api/supplier/outlets/{$this->outlet->id}/purchase-orders",
            [
                'supplier_id' => $this->supplier->id,
                'order_date' => '2026-08-01',
                'items' => $apiItems,
            ]
        );

        $response->assertStatus(201);
        $this->assertEquals(
            $expectedTotal,
            (float) $response->json('data.total_amount'),
            'PO total does not equal sum of qty × unit_cost'
        );
    }

    // =========================================================================
    // Property 8: New PO always starts with status=draft, payment_status=unpaid
    // Validates: Requirements 2.5, 2.7
    // =========================================================================

    /**
     * Property 8: Every newly created PO has status=draft and payment_status=unpaid.
     *
     * **Validates: Requirements 2.5, 2.7**
     */
    public function test_property8_initial_state_invariant(): void
    {
        $scenarios = [
            ['order_date' => '2026-01-15', 'items' => 1],
            ['order_date' => '2026-06-20', 'items' => 3],
            ['order_date' => '2026-12-31', 'items' => 2],
        ];

        foreach ($scenarios as $scenario) {
            $items = [];
            for ($i = 0; $i < $scenario['items']; $i++) {
                $items[] = [
                    'product_variant_id' => $i + 1,
                    'product_name' => "Product {$i}",
                    'variant_name' => 'Default',
                    'quantity' => rand(1, 50),
                    'unit_cost' => rand(1000, 100000),
                ];
            }

            $response = $this->actingAs($this->user)->postJson(
                "/api/supplier/outlets/{$this->outlet->id}/purchase-orders",
                [
                    'supplier_id' => $this->supplier->id,
                    'order_date' => $scenario['order_date'],
                    'items' => $items,
                ]
            );

            $response->assertStatus(201);
            $this->assertEquals('draft', $response->json('data.status'));
            $this->assertEquals('unpaid', $response->json('data.payment_status'));
        }
    }

    // =========================================================================
    // Property 9: Only draft POs are editable
    // Validates: Requirements 2.6, 2.8
    // =========================================================================

    public static function nonEditableStatusProvider(): array
    {
        return [
            'confirmed' => ['confirmed'],
            'partial' => ['partial'],
            'received' => ['received'],
            'cancelled' => ['cancelled'],
        ];
    }

    /**
     * Property 9: Non-draft POs reject edits.
     *
     * **Validates: Requirements 2.6, 2.8**
     */
    #[DataProvider('nonEditableStatusProvider')]
    public function test_property9_non_draft_rejects_edits(string $status): void
    {
        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-001',
            'order_date' => '2026-08-01',
            'status' => $status,
            'payment_status' => 'unpaid',
            'total_amount' => 100000,
            'cancelled_at' => $status === 'cancelled' ? now() : null,
        ]);

        PurchaseOrderItemModel::create([
            'purchase_order_id' => $po->id,
            'product_variant_id' => 1,
            'product_name' => 'Product',
            'variant_name' => 'Default',
            'quantity' => 10,
            'unit_cost' => 10000,
            'subtotal' => 100000,
            'received_quantity' => 0,
        ]);

        $response = $this->actingAs($this->user)->putJson(
            "/api/supplier/purchase-orders/{$po->id}",
            [
                'supplier_id' => $this->supplier->id,
                'order_date' => '2026-08-02',
                'items' => [
                    [
                        'product_variant_id' => 1,
                        'product_name' => 'Changed',
                        'variant_name' => 'Default',
                        'quantity' => 99,
                        'unit_cost' => 99999,
                    ],
                ],
            ]
        );

        $response->assertStatus(422);
    }

    /**
     * Property 9: Draft POs accept edits.
     *
     * **Validates: Requirements 2.6, 2.8**
     */
    public function test_property9_draft_accepts_edits(): void
    {
        $response = $this->actingAs($this->user)->postJson(
            "/api/supplier/outlets/{$this->outlet->id}/purchase-orders",
            [
                'supplier_id' => $this->supplier->id,
                'order_date' => '2026-08-01',
                'items' => [
                    [
                        'product_variant_id' => 1,
                        'product_name' => 'Product A',
                        'variant_name' => 'Default',
                        'quantity' => 5,
                        'unit_cost' => 10000,
                    ],
                ],
            ]
        );

        $poId = $response->json('data.id');

        $updateResponse = $this->actingAs($this->user)->putJson(
            "/api/supplier/purchase-orders/{$poId}",
            [
                'supplier_id' => $this->supplier->id,
                'order_date' => '2026-08-05',
                'notes' => 'Updated',
                'items' => [
                    [
                        'product_variant_id' => 2,
                        'product_name' => 'Product B',
                        'variant_name' => 'Large',
                        'quantity' => 20,
                        'unit_cost' => 25000,
                    ],
                ],
            ]
        );

        $updateResponse->assertOk();
    }

    // =========================================================================
    // Property 10: PO with 0 items cannot be confirmed; PO with ≥1 item can
    // Validates: Requirements 2.7, 2.9
    // =========================================================================

    /**
     * Property 10: Empty PO cannot be confirmed.
     *
     * **Validates: Requirements 2.7, 2.9**
     */
    public function test_property10_empty_po_cannot_be_confirmed(): void
    {
        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-001',
            'order_date' => '2026-08-01',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'total_amount' => 0,
        ]);

        $response = $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/confirm"
        );

        $response->assertStatus(422);
        $this->assertDatabaseHas('supplier_purchase_orders', [
            'id' => $po->id,
            'status' => 'draft',
        ]);
    }

    /**
     * Property 10: PO with items can be confirmed.
     *
     * **Validates: Requirements 2.7, 2.9**
     */
    public function test_property10_po_with_items_can_be_confirmed(): void
    {
        $itemCounts = [1, 3, 5];

        foreach ($itemCounts as $idx => $count) {
            $po = PurchaseOrderModel::create([
                'outlet_id' => $this->outlet->id,
                'supplier_id' => $this->supplier->id,
                'po_number' => "PO-20260801-" . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'order_date' => '2026-08-01',
                'status' => 'draft',
                'payment_status' => 'unpaid',
                'total_amount' => 0,
            ]);

            $total = 0;
            for ($i = 0; $i < $count; $i++) {
                $subtotal = 10 * 5000;
                PurchaseOrderItemModel::create([
                    'purchase_order_id' => $po->id,
                    'product_variant_id' => $i + 1,
                    'product_name' => "Product {$i}",
                    'variant_name' => 'Default',
                    'quantity' => 10,
                    'unit_cost' => 5000,
                    'subtotal' => $subtotal,
                    'received_quantity' => 0,
                ]);
                $total += $subtotal;
            }
            $po->update(['total_amount' => $total]);

            $response = $this->actingAs($this->user)->postJson(
                "/api/supplier/purchase-orders/{$po->id}/confirm"
            );

            $response->assertOk();
            $this->assertDatabaseHas('supplier_purchase_orders', [
                'id' => $po->id,
                'status' => 'confirmed',
            ]);
        }
    }

    // =========================================================================
    // Property 11: Only draft/confirmed POs can be cancelled
    // Validates: Requirements 3.1, 3.2, 3.3
    // =========================================================================

    public static function cancellableStatusProvider(): array
    {
        return [
            'draft' => ['draft'],
            'confirmed' => ['confirmed'],
        ];
    }

    public static function nonCancellableStatusProvider(): array
    {
        return [
            'partial' => ['partial'],
            'received' => ['received'],
        ];
    }

    /**
     * Property 11: Draft/confirmed POs can be cancelled.
     *
     * **Validates: Requirements 3.1, 3.2, 3.3**
     */
    #[DataProvider('cancellableStatusProvider')]
    public function test_property11_cancellable_states_succeed(string $status): void
    {
        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-001',
            'order_date' => '2026-08-01',
            'status' => $status,
            'payment_status' => 'unpaid',
            'total_amount' => 100000,
        ]);

        PurchaseOrderItemModel::create([
            'purchase_order_id' => $po->id,
            'product_variant_id' => 1,
            'product_name' => 'Product',
            'variant_name' => 'Default',
            'quantity' => 10,
            'unit_cost' => 10000,
            'subtotal' => 100000,
            'received_quantity' => 0,
        ]);

        $response = $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/cancel"
        );

        $response->assertOk();
        $this->assertDatabaseHas('supplier_purchase_orders', [
            'id' => $po->id,
            'status' => 'cancelled',
        ]);

        // Cancelled_at should be set
        $po->refresh();
        $this->assertNotNull($po->cancelled_at);
    }

    /**
     * Property 11: Partial/received POs reject cancellation.
     *
     * **Validates: Requirements 3.1, 3.2, 3.3**
     */
    #[DataProvider('nonCancellableStatusProvider')]
    public function test_property11_non_cancellable_states_reject(string $status): void
    {
        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-001',
            'order_date' => '2026-08-01',
            'status' => $status,
            'payment_status' => 'unpaid',
            'total_amount' => 100000,
        ]);

        $response = $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/cancel"
        );

        $response->assertStatus(422);
        $this->assertDatabaseHas('supplier_purchase_orders', [
            'id' => $po->id,
            'status' => $status,
        ]);
    }

    // =========================================================================
    // Property 12: Cancelled POs never count towards outstanding debt
    // Validates: Requirements 3.4
    // =========================================================================

    /**
     * Property 12: Cancelled POs do not contribute to supplier outstanding debt.
     *
     * **Validates: Requirements 3.4**
     */
    public function test_property12_cancelled_po_excluded_from_debt(): void
    {
        // Create confirmed PO (should count)
        PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-001',
            'order_date' => '2026-08-01',
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'total_amount' => 200000,
        ]);

        // Create cancelled PO (should NOT count)
        PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-002',
            'order_date' => '2026-08-01',
            'status' => 'cancelled',
            'payment_status' => 'unpaid',
            'total_amount' => 500000,
            'cancelled_at' => now(),
        ]);

        // Create another cancelled PO
        PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-003',
            'order_date' => '2026-08-01',
            'status' => 'cancelled',
            'payment_status' => 'unpaid',
            'total_amount' => 300000,
            'cancelled_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->getJson(
            "/api/supplier/suppliers/{$this->supplier->id}"
        );

        $response->assertOk();
        // Only the confirmed PO (200000) should count
        $this->assertEquals(200000, $response->json('data.total_debt'));
    }

    /**
     * Property 12: After cancelling a PO, debt recalculates excluding it.
     *
     * **Validates: Requirements 3.4**
     */
    public function test_property12_debt_decreases_after_cancellation(): void
    {
        $po1 = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-001',
            'order_date' => '2026-08-01',
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'total_amount' => 150000,
        ]);

        PurchaseOrderItemModel::create([
            'purchase_order_id' => $po1->id,
            'product_variant_id' => 1,
            'product_name' => 'P1',
            'variant_name' => 'Default',
            'quantity' => 15,
            'unit_cost' => 10000,
            'subtotal' => 150000,
            'received_quantity' => 0,
        ]);

        $po2 = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-002',
            'order_date' => '2026-08-01',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'total_amount' => 250000,
        ]);

        PurchaseOrderItemModel::create([
            'purchase_order_id' => $po2->id,
            'product_variant_id' => 2,
            'product_name' => 'P2',
            'variant_name' => 'Default',
            'quantity' => 25,
            'unit_cost' => 10000,
            'subtotal' => 250000,
            'received_quantity' => 0,
        ]);

        // Check debt before cancel
        $response = $this->actingAs($this->user)->getJson("/api/supplier/suppliers/{$this->supplier->id}");
        $debtBefore = (float) $response->json('data.total_debt');

        // Cancel the draft PO
        $this->actingAs($this->user)->postJson("/api/supplier/purchase-orders/{$po2->id}/cancel")
            ->assertOk();

        // Check debt after cancel
        $response = $this->actingAs($this->user)->getJson("/api/supplier/suppliers/{$this->supplier->id}");
        $debtAfter = (float) $response->json('data.total_debt');

        $this->assertLessThan($debtBefore, $debtAfter);
    }
}
