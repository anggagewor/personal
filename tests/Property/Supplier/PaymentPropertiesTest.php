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
use Modules\Supplier\Infrastructure\Models\SupplierPaymentModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Property tests for Payment Tracking (Properties 17-19).
 */
class PaymentPropertiesTest extends TestCase
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
        ProductVariantModel::create([
            'product_id' => $product->id,
            'name' => 'Default',
            'price' => 10000,
            'stock_quantity' => 50,
        ]);
    }

    private function createConfirmedPO(float $totalAmount): PurchaseOrderModel
    {
        static $seq = 0;
        $seq++;

        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-' . str_pad($seq, 3, '0', STR_PAD_LEFT),
            'order_date' => '2026-08-01',
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'total_amount' => $totalAmount,
        ]);

        PurchaseOrderItemModel::create([
            'purchase_order_id' => $po->id,
            'product_variant_id' => 1,
            'product_name' => 'Product',
            'variant_name' => 'Default',
            'quantity' => 1,
            'unit_cost' => $totalAmount,
            'subtotal' => $totalAmount,
            'received_quantity' => 0,
        ]);

        return $po;
    }

    // =========================================================================
    // Property 17: Payment status determination
    // Validates: Requirements 6.2
    // =========================================================================

    /**
     * Property 17: Full payment → "paid".
     *
     * **Validates: Requirements 6.2**
     */
    public function test_property17_full_payment_status_paid(): void
    {
        $totals = [50000, 100000, 250000];

        foreach ($totals as $total) {
            $po = $this->createConfirmedPO($total);

            $this->actingAs($this->user)->postJson(
                "/api/supplier/purchase-orders/{$po->id}/payments",
                [
                    'amount' => $total,
                    'payment_date' => '2026-08-05',
                    'payment_method' => 'bank_transfer',
                ]
            )->assertStatus(201);

            $po->refresh();
            $this->assertEquals('paid', $po->payment_status, "PO with total {$total} should be 'paid' after full payment");
        }
    }

    /**
     * Property 17: Partial payment → "partial".
     *
     * **Validates: Requirements 6.2**
     */
    public function test_property17_partial_payment_status_partial(): void
    {
        $po = $this->createConfirmedPO(100000);

        // Pay less than total
        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/payments",
            [
                'amount' => 40000,
                'payment_date' => '2026-08-05',
                'payment_method' => 'cash',
            ]
        )->assertStatus(201);

        $po->refresh();
        $this->assertEquals('partial', $po->payment_status);
    }

    /**
     * Property 17: No payment → "unpaid".
     *
     * **Validates: Requirements 6.2**
     */
    public function test_property17_no_payment_status_unpaid(): void
    {
        $po = $this->createConfirmedPO(100000);

        $po->refresh();
        $this->assertEquals('unpaid', $po->payment_status);
    }

    /**
     * Property 17: Installments that sum to total → "paid".
     *
     * **Validates: Requirements 6.2**
     */
    public function test_property17_installments_to_paid(): void
    {
        $po = $this->createConfirmedPO(120000);

        $installments = [30000, 30000, 30000, 30000];

        foreach ($installments as $i => $amount) {
            $this->actingAs($this->user)->postJson(
                "/api/supplier/purchase-orders/{$po->id}/payments",
                [
                    'amount' => $amount,
                    'payment_date' => '2026-08-' . str_pad($i + 5, 2, '0', STR_PAD_LEFT),
                    'payment_method' => 'cash',
                ]
            )->assertStatus(201);
        }

        $po->refresh();
        $this->assertEquals('paid', $po->payment_status);
    }

    // =========================================================================
    // Property 18: Overpayment prevention
    // Validates: Requirements 6.3
    // =========================================================================

    /**
     * Property 18: Payment exceeding outstanding balance is rejected.
     *
     * **Validates: Requirements 6.3**
     */
    public function test_property18_overpayment_rejected(): void
    {
        $po = $this->createConfirmedPO(100000);

        $response = $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/payments",
            [
                'amount' => 100001,
                'payment_date' => '2026-08-05',
                'payment_method' => 'cash',
            ]
        );

        $response->assertStatus(422);
        $this->assertDatabaseMissing('supplier_payments', [
            'purchase_order_id' => $po->id,
        ]);
    }

    /**
     * Property 18: Overpayment after partial payment is rejected.
     *
     * **Validates: Requirements 6.3**
     */
    public function test_property18_overpayment_after_partial(): void
    {
        $po = $this->createConfirmedPO(100000);

        // First payment: 60000
        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/payments",
            [
                'amount' => 60000,
                'payment_date' => '2026-08-05',
                'payment_method' => 'cash',
            ]
        )->assertStatus(201);

        // Try to pay 50000 (remaining is only 40000)
        $response = $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/payments",
            [
                'amount' => 50000,
                'payment_date' => '2026-08-10',
                'payment_method' => 'bank_transfer',
            ]
        );

        $response->assertStatus(422);
    }

    /**
     * Property 18: Exact remaining amount is accepted.
     *
     * **Validates: Requirements 6.3**
     */
    public function test_property18_exact_remaining_accepted(): void
    {
        $po = $this->createConfirmedPO(100000);

        // First payment
        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/payments",
            [
                'amount' => 70000,
                'payment_date' => '2026-08-05',
                'payment_method' => 'cash',
            ]
        )->assertStatus(201);

        // Exact remaining: 30000
        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/payments",
            [
                'amount' => 30000,
                'payment_date' => '2026-08-10',
                'payment_method' => 'bank_transfer',
            ]
        )->assertStatus(201);

        $po->refresh();
        $this->assertEquals('paid', $po->payment_status);
    }

    // =========================================================================
    // Property 19: Outstanding debt = sum(non-cancelled PO totals) - sum(payments)
    // Validates: Requirements 6.4, 6.7
    // =========================================================================

    /**
     * Property 19: Outstanding debt calculation is correct.
     *
     * **Validates: Requirements 6.4, 6.7**
     */
    public function test_property19_outstanding_debt_calculation(): void
    {
        // PO 1: confirmed, 200000, paid 50000
        $po1 = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-101',
            'order_date' => '2026-08-01',
            'status' => 'confirmed',
            'payment_status' => 'partial',
            'total_amount' => 200000,
        ]);
        SupplierPaymentModel::create([
            'purchase_order_id' => $po1->id,
            'amount' => 50000,
            'payment_date' => '2026-08-05',
            'payment_method' => 'cash',
        ]);

        // PO 2: received, 300000, paid 300000 (fully paid)
        $po2 = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-102',
            'order_date' => '2026-08-02',
            'status' => 'received',
            'payment_status' => 'paid',
            'total_amount' => 300000,
        ]);
        SupplierPaymentModel::create([
            'purchase_order_id' => $po2->id,
            'amount' => 300000,
            'payment_date' => '2026-08-06',
            'payment_method' => 'bank_transfer',
        ]);

        // PO 3: cancelled, 500000 (should not count)
        PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-103',
            'order_date' => '2026-08-03',
            'status' => 'cancelled',
            'payment_status' => 'unpaid',
            'total_amount' => 500000,
            'cancelled_at' => now(),
        ]);

        // PO 4: draft, 100000 (should count)
        PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-104',
            'order_date' => '2026-08-04',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'total_amount' => 100000,
        ]);

        // Expected debt: (200000 - 50000) + (300000 - 300000) + 100000 = 150000 + 0 + 100000 = 250000
        $response = $this->actingAs($this->user)->getJson("/api/supplier/suppliers/{$this->supplier->id}");
        $response->assertOk();

        $this->assertEquals(250000, (float) $response->json('data.total_debt'));
    }

    /**
     * Property 19: Supplier with no POs has zero debt.
     *
     * **Validates: Requirements 6.4, 6.7**
     */
    public function test_property19_no_pos_zero_debt(): void
    {
        $emptySupplier = SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Empty Supplier',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/supplier/suppliers/{$emptySupplier->id}");
        $response->assertOk();
        $this->assertEquals(0, (float) $response->json('data.total_debt'));
    }

    /**
     * Property 19: Debt decreases as payments are recorded.
     *
     * **Validates: Requirements 6.4, 6.7**
     */
    public function test_property19_debt_decreases_with_payments(): void
    {
        $po = $this->createConfirmedPO(200000);

        // Initial debt
        $response = $this->actingAs($this->user)->getJson("/api/supplier/suppliers/{$this->supplier->id}");
        $this->assertEquals(200000, (float) $response->json('data.total_debt'));

        // Pay 80000
        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/payments",
            [
                'amount' => 80000,
                'payment_date' => '2026-08-05',
                'payment_method' => 'cash',
            ]
        )->assertStatus(201);

        $response = $this->actingAs($this->user)->getJson("/api/supplier/suppliers/{$this->supplier->id}");
        $this->assertEquals(120000, (float) $response->json('data.total_debt'));

        // Pay remaining
        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$po->id}/payments",
            [
                'amount' => 120000,
                'payment_date' => '2026-08-10',
                'payment_method' => 'bank_transfer',
            ]
        )->assertStatus(201);

        $response = $this->actingAs($this->user)->getJson("/api/supplier/suppliers/{$this->supplier->id}");
        $this->assertEquals(0, (float) $response->json('data.total_debt'));
    }
}
