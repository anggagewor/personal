<?php

namespace Tests\Property\Supplier;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderItemModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderModel;
use Modules\Supplier\Infrastructure\Models\SupplierModel;
use Modules\Supplier\Infrastructure\Models\SupplierPaymentModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Tests\TestCase;

/**
 * Property tests for Reports and Ordering (Properties 23-24).
 */
class ReportPropertiesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private OutletModel $outlet;
    private SupplierModel $supplier1;
    private SupplierModel $supplier2;

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
            'name' => 'Supplier Alpha',
        ]);
        $this->supplier2 = SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Supplier Beta',
        ]);
    }

    private function createPO(
        int $supplierId,
        string $status,
        float $totalAmount,
        string $orderDate,
        array $items = []
    ): PurchaseOrderModel {
        static $seq = 0;
        $seq++;

        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $supplierId,
            'po_number' => 'PO-' . str_replace('-', '', $orderDate) . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT),
            'order_date' => $orderDate,
            'status' => $status,
            'payment_status' => 'unpaid',
            'total_amount' => $totalAmount,
            'cancelled_at' => $status === 'cancelled' ? now() : null,
        ]);

        if (empty($items)) {
            // Create a default item
            PurchaseOrderItemModel::create([
                'purchase_order_id' => $po->id,
                'product_variant_id' => 1,
                'product_name' => 'Default Product',
                'variant_name' => 'Default',
                'quantity' => 1,
                'unit_cost' => $totalAmount,
                'subtotal' => $totalAmount,
                'received_quantity' => 0,
            ]);
        } else {
            foreach ($items as $item) {
                PurchaseOrderItemModel::create(array_merge([
                    'purchase_order_id' => $po->id,
                    'received_quantity' => 0,
                ], $item));
            }
        }

        return $po;
    }

    // =========================================================================
    // Property 23: Report aggregation consistency
    // Summary total = sum of by-supplier totals = sum of by-product totals
    // Validates: Requirements 8.1, 8.2, 8.3
    // =========================================================================

    /**
     * Property 23: Summary total equals sum of by-supplier totals.
     *
     * **Validates: Requirements 8.1, 8.2, 8.3**
     */
    public function test_property23_summary_equals_supplier_sum(): void
    {
        // Create POs for different suppliers
        $this->createPO($this->supplier1->id, 'confirmed', 100000, '2026-08-01');
        $this->createPO($this->supplier1->id, 'received', 75000, '2026-08-05');
        $this->createPO($this->supplier2->id, 'confirmed', 200000, '2026-08-03');
        // Cancelled PO should not count
        $this->createPO($this->supplier2->id, 'cancelled', 300000, '2026-08-04');

        // Get summary
        $summaryResponse = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/reports/summary"
        );
        $summaryResponse->assertOk();
        $summaryTotal = (float) $summaryResponse->json('data.total_purchase_value');

        // Get by-supplier
        $bySupplierResponse = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/reports/by-supplier"
        );
        $bySupplierResponse->assertOk();
        $supplierSum = collect($bySupplierResponse->json('data'))->sum('total_purchase');

        $this->assertEquals($summaryTotal, $supplierSum, 'Summary total must equal sum of by-supplier totals');
    }

    /**
     * Property 23: Summary total equals sum of by-product totals.
     *
     * **Validates: Requirements 8.1, 8.2, 8.3**
     */
    public function test_property23_summary_equals_product_sum(): void
    {
        // Create POs with multiple products
        $this->createPO($this->supplier1->id, 'confirmed', 150000, '2026-08-01', [
            [
                'product_variant_id' => 1,
                'product_name' => 'Product A',
                'variant_name' => 'Default',
                'quantity' => 10,
                'unit_cost' => 5000,
                'subtotal' => 50000,
            ],
            [
                'product_variant_id' => 2,
                'product_name' => 'Product B',
                'variant_name' => 'Default',
                'quantity' => 5,
                'unit_cost' => 20000,
                'subtotal' => 100000,
            ],
        ]);

        $this->createPO($this->supplier2->id, 'received', 90000, '2026-08-02', [
            [
                'product_variant_id' => 1,
                'product_name' => 'Product A',
                'variant_name' => 'Default',
                'quantity' => 6,
                'unit_cost' => 5000,
                'subtotal' => 30000,
            ],
            [
                'product_variant_id' => 3,
                'product_name' => 'Product C',
                'variant_name' => 'Default',
                'quantity' => 3,
                'unit_cost' => 20000,
                'subtotal' => 60000,
            ],
        ]);

        // Cancelled — should not count
        $this->createPO($this->supplier1->id, 'cancelled', 200000, '2026-08-03');

        // Get summary
        $summaryResponse = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/reports/summary"
        );
        $summaryResponse->assertOk();
        $summaryTotal = (float) $summaryResponse->json('data.total_purchase_value');

        // Get by-product
        $byProductResponse = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/reports/by-product"
        );
        $byProductResponse->assertOk();
        $productSum = collect($byProductResponse->json('data'))->sum('total_cost');

        $this->assertEquals($summaryTotal, $productSum, 'Summary total must equal sum of by-product totals');
    }

    /**
     * Property 23: Cancelled POs excluded from all report views.
     *
     * **Validates: Requirements 8.1, 8.2, 8.3**
     */
    public function test_property23_cancelled_excluded_from_all_views(): void
    {
        $this->createPO($this->supplier1->id, 'confirmed', 100000, '2026-08-01');
        $this->createPO($this->supplier1->id, 'cancelled', 999999, '2026-08-02');

        $summaryResponse = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/reports/summary"
        );
        $summaryTotal = (float) $summaryResponse->json('data.total_purchase_value');
        $this->assertEquals(100000, $summaryTotal, 'Cancelled POs must be excluded from summary');

        $bySupplierResponse = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/reports/by-supplier"
        );
        $supplierSum = collect($bySupplierResponse->json('data'))->sum('total_purchase');
        $this->assertEquals(100000, $supplierSum, 'Cancelled POs must be excluded from by-supplier');
    }

    /**
     * Property 23: Date range filtering is consistent across views.
     *
     * **Validates: Requirements 8.1, 8.2, 8.3**
     */
    public function test_property23_date_range_consistency(): void
    {
        // POs in different months
        $this->createPO($this->supplier1->id, 'confirmed', 50000, '2026-07-15');
        $this->createPO($this->supplier1->id, 'confirmed', 80000, '2026-08-10');
        $this->createPO($this->supplier2->id, 'received', 60000, '2026-08-20');

        $dateFrom = '2026-08-01';
        $dateTo = '2026-08-31';

        // Summary filtered
        $summaryResponse = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/reports/summary?date_from={$dateFrom}&date_to={$dateTo}"
        );
        $summaryTotal = (float) $summaryResponse->json('data.total_purchase_value');

        // By-supplier filtered
        $bySupplierResponse = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/reports/by-supplier?date_from={$dateFrom}&date_to={$dateTo}"
        );
        $supplierSum = collect($bySupplierResponse->json('data'))->sum('total_purchase');

        $this->assertEquals($summaryTotal, $supplierSum);
        // Only August POs: 80000 + 60000 = 140000
        $this->assertEquals(140000, $summaryTotal);
    }

    // =========================================================================
    // Property 24: PO list is always ordered by date descending
    // Validates: Requirements 5.1
    // =========================================================================

    /**
     * Property 24: PO list ordered by date descending.
     *
     * **Validates: Requirements 5.1**
     */
    public function test_property24_po_list_ordered_by_date_desc(): void
    {
        // Create POs with various dates in random order
        $dates = ['2026-08-15', '2026-07-01', '2026-09-20', '2026-08-01', '2026-06-10'];

        foreach ($dates as $date) {
            $this->createPO($this->supplier1->id, 'confirmed', 50000, $date);
        }

        $response = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/purchase-orders"
        );

        $response->assertOk();
        $orders = $response->json('data');

        // Verify descending order
        for ($i = 1; $i < count($orders); $i++) {
            $currentDate = $orders[$i - 1]['order_date'];
            $nextDate = $orders[$i]['order_date'];
            $this->assertGreaterThanOrEqual(
                $nextDate,
                $currentDate,
                "PO at index " . ($i - 1) . " (date: {$currentDate}) should come before index {$i} (date: {$nextDate})"
            );
        }
    }

    /**
     * Property 24: Ordering maintained across pagination.
     *
     * **Validates: Requirements 5.1**
     */
    public function test_property24_ordering_across_pages(): void
    {
        // Create 15 POs with different dates
        for ($i = 1; $i <= 15; $i++) {
            $date = sprintf('2026-%02d-%02d', rand(1, 12), rand(1, 28));
            $this->createPO($this->supplier1->id, 'confirmed', $i * 10000, $date);
        }

        // Get page 1
        $response1 = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/purchase-orders?per_page=10&page=1"
        );
        $response1->assertOk();
        $page1 = $response1->json('data');

        // Get page 2
        $response2 = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/purchase-orders?per_page=10&page=2"
        );
        $response2->assertOk();
        $page2 = $response2->json('data');

        // Within page 1, dates are descending
        for ($i = 1; $i < count($page1); $i++) {
            $this->assertGreaterThanOrEqual($page1[$i]['order_date'], $page1[$i - 1]['order_date']);
        }

        // Within page 2, dates are descending
        for ($i = 1; $i < count($page2); $i++) {
            $this->assertGreaterThanOrEqual($page2[$i]['order_date'], $page2[$i - 1]['order_date']);
        }

        // Last item of page 1 is >= first item of page 2 (cross-page ordering)
        if (count($page1) > 0 && count($page2) > 0) {
            $lastPage1Date = end($page1)['order_date'];
            $firstPage2Date = $page2[0]['order_date'];
            $this->assertGreaterThanOrEqual($firstPage2Date, $lastPage1Date);
        }
    }

    /**
     * Property 24: Different statuses don't affect ordering.
     *
     * **Validates: Requirements 5.1**
     */
    public function test_property24_ordering_independent_of_status(): void
    {
        $this->createPO($this->supplier1->id, 'draft', 30000, '2026-08-01');
        $this->createPO($this->supplier1->id, 'confirmed', 50000, '2026-08-10');
        $this->createPO($this->supplier1->id, 'received', 40000, '2026-08-05');
        $this->createPO($this->supplier1->id, 'cancelled', 20000, '2026-08-15');
        $this->createPO($this->supplier1->id, 'partial', 60000, '2026-08-03');

        $response = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/purchase-orders"
        );
        $response->assertOk();
        $orders = $response->json('data');

        // Verify descending date order regardless of status
        for ($i = 1; $i < count($orders); $i++) {
            $this->assertGreaterThanOrEqual(
                $orders[$i]['order_date'],
                $orders[$i - 1]['order_date']
            );
        }
    }
}
