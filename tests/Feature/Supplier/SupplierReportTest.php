<?php

namespace Tests\Feature\Supplier;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderItemModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderModel;
use Modules\Supplier\Infrastructure\Models\SupplierModel;
use Modules\Supplier\Infrastructure\Models\SupplierPaymentModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Tests\TestCase;

class SupplierReportTest extends TestCase
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
            'name' => 'Outlet Report Test',
            'business_type' => 'kafe',
            'payment_flow' => 'pay_first',
        ]);

        $this->supplier = SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Supplier A',
            'phone' => '08123456789',
        ]);
    }

    public function test_purchase_summary_returns_totals(): void
    {
        $po1 = $this->createPurchaseOrder('confirmed', 100000);
        $po2 = $this->createPurchaseOrder('confirmed', 50000);

        // Record a payment for po1
        SupplierPaymentModel::create([
            'purchase_order_id' => $po1->id,
            'amount' => 40000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/reports/summary"
        );

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals(150000, $data['total_purchase_value']);
        $this->assertEquals(40000, $data['total_paid']);
        $this->assertEquals(110000, $data['total_outstanding_debt']);
        $this->assertEquals(2, $data['purchase_count']);
    }

    public function test_summary_filters_by_date_range(): void
    {
        // PO in January
        $this->createPurchaseOrder('confirmed', 80000, '2026-01-15');
        // PO in March
        $this->createPurchaseOrder('confirmed', 60000, '2026-03-10');

        $response = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/reports/summary?date_from=2026-03-01&date_to=2026-03-31"
        );

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals(60000, $data['total_purchase_value']);
        $this->assertEquals(1, $data['purchase_count']);
    }

    public function test_by_supplier_groups_correctly(): void
    {
        $supplierB = SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Supplier B',
            'phone' => '08199999999',
        ]);

        $this->createPurchaseOrder('confirmed', 100000);
        $this->createPurchaseOrder('confirmed', 50000, null, $supplierB->id);

        $response = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/reports/by-supplier"
        );

        $response->assertOk();

        $data = $response->json('data');
        $this->assertCount(2, $data);

        $supplierAData = collect($data)->firstWhere('supplier_id', $this->supplier->id);
        $supplierBData = collect($data)->firstWhere('supplier_id', $supplierB->id);

        $this->assertNotNull($supplierAData);
        $this->assertEquals('Supplier A', $supplierAData['supplier_name']);
        $this->assertEquals(100000, $supplierAData['total_purchase']);

        $this->assertNotNull($supplierBData);
        $this->assertEquals('Supplier B', $supplierBData['supplier_name']);
        $this->assertEquals(50000, $supplierBData['total_purchase']);
    }

    public function test_by_product_groups_correctly(): void
    {
        $po = $this->createPurchaseOrder('confirmed', 0);

        PurchaseOrderItemModel::create([
            'purchase_order_id' => $po->id,
            'product_variant_id' => 1,
            'product_name' => 'Product A',
            'variant_name' => 'Reguler',
            'quantity' => 10,
            'unit_cost' => 5000,
            'subtotal' => 50000,
            'received_quantity' => 0,
        ]);

        PurchaseOrderItemModel::create([
            'purchase_order_id' => $po->id,
            'product_variant_id' => 2,
            'product_name' => 'Product B',
            'variant_name' => 'Large',
            'quantity' => 5,
            'unit_cost' => 10000,
            'subtotal' => 50000,
            'received_quantity' => 0,
        ]);

        // Update PO total
        $po->update(['total_amount' => 100000]);

        $response = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/reports/by-product"
        );

        $response->assertOk();

        $data = $response->json('data');
        $this->assertCount(2, $data);

        $productA = collect($data)->firstWhere('product_variant_id', 1);
        $productB = collect($data)->firstWhere('product_variant_id', 2);

        $this->assertNotNull($productA);
        $this->assertEquals('Product A', $productA['product_name']);
        $this->assertEquals(10, $productA['total_quantity']);
        $this->assertEquals(50000, $productA['total_cost']);

        $this->assertNotNull($productB);
        $this->assertEquals('Product B', $productB['product_name']);
        $this->assertEquals(5, $productB['total_quantity']);
        $this->assertEquals(50000, $productB['total_cost']);
    }

    public function test_cancelled_pos_excluded_from_summary(): void
    {
        $this->createPurchaseOrder('confirmed', 100000);
        $this->createPurchaseOrder('cancelled', 75000);

        $response = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/reports/summary"
        );

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals(100000, $data['total_purchase_value']);
        $this->assertEquals(1, $data['purchase_count']);
    }

    public function test_csv_export_returns_downloadable_file(): void
    {
        $this->createPurchaseOrder('confirmed', 100000);

        $response = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/reports/export?type=summary"
        );

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('total_purchase_value', $content);
        $this->assertStringContainsString('100000', $content);
    }

    public function test_dashboard_returns_widget_data(): void
    {
        $this->createPurchaseOrder('confirmed', 80000);
        $this->createPurchaseOrder('partial', 40000);
        $this->createPurchaseOrder('received', 20000);

        // Pay off the received PO
        SupplierPaymentModel::create([
            'purchase_order_id' => PurchaseOrderModel::where('status', 'received')->first()->id,
            'amount' => 20000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
        ]);

        $response = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/dashboard"
        );

        $response->assertOk();

        $data = $response->json('data');
        $this->assertArrayHasKey('total_outstanding_debt', $data);
        $this->assertArrayHasKey('pending_po_count', $data);
        $this->assertArrayHasKey('recent_purchase_orders', $data);

        // Total debt: 80000 + 40000 + 20000 - 20000 (paid) = 120000
        $this->assertEquals(120000, $data['total_outstanding_debt']);
        // Pending: confirmed + partial = 2
        $this->assertEquals(2, $data['pending_po_count']);
        $this->assertIsArray($data['recent_purchase_orders']);
    }

    private function createPurchaseOrder(
        string $status,
        float $totalAmount,
        ?string $orderDate = null,
        ?int $supplierId = null
    ): PurchaseOrderModel {
        static $seq = 0;
        $seq++;

        $date = $orderDate ?? now()->toDateString();

        $po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $supplierId ?? $this->supplier->id,
            'po_number' => 'PO-' . str_replace('-', '', $date) . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT),
            'order_date' => $date,
            'status' => $status,
            'payment_status' => 'unpaid',
            'total_amount' => $totalAmount,
        ]);

        // Create a default item for POs that have a total (for by-product query consistency)
        if ($totalAmount > 0 && $status !== 'cancelled') {
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
        }

        return $po;
    }
}
