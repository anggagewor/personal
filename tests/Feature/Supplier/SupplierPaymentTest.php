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

class SupplierPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private OutletModel $outlet;
    private SupplierModel $supplier;
    private PurchaseOrderModel $po;

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

        $variant = ProductVariantModel::create([
            'product_id' => $product->id,
            'name' => 'Default',
            'price' => 10000,
            'stock_quantity' => 50,
        ]);

        // Create a confirmed PO with total_amount = 100000
        $this->po = PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-20260801-001',
            'order_date' => '2026-08-01',
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'total_amount' => 100000,
        ]);

        PurchaseOrderItemModel::create([
            'purchase_order_id' => $this->po->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Test Product',
            'variant_name' => 'Default',
            'quantity' => 10,
            'unit_cost' => 10000,
            'subtotal' => 100000,
            'received_quantity' => 0,
        ]);
    }

    public function test_can_record_single_payment(): void
    {
        $response = $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$this->po->id}/payments",
            [
                'amount' => 50000,
                'payment_date' => '2026-08-05',
                'payment_method' => 'cash',
                'notes' => 'Pembayaran pertama',
            ]
        );

        $response->assertStatus(201);
        $response->assertJsonPath('data.amount', 50000);
        $response->assertJsonPath('data.payment_method', 'cash');

        $this->assertDatabaseHas('supplier_payments', [
            'purchase_order_id' => $this->po->id,
            'amount' => 50000,
            'payment_method' => 'cash',
        ]);

        // payment_status should be partial
        $this->po->refresh();
        $this->assertEquals('partial', $this->po->payment_status);
    }

    public function test_can_record_installment_payments(): void
    {
        // First partial payment
        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$this->po->id}/payments",
            [
                'amount' => 30000,
                'payment_date' => '2026-08-05',
                'payment_method' => 'bank_transfer',
            ]
        )->assertStatus(201);

        $this->po->refresh();
        $this->assertEquals('partial', $this->po->payment_status);

        // Second partial payment
        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$this->po->id}/payments",
            [
                'amount' => 30000,
                'payment_date' => '2026-08-10',
                'payment_method' => 'cash',
            ]
        )->assertStatus(201);

        $this->po->refresh();
        $this->assertEquals('partial', $this->po->payment_status);

        // Final payment — total now 100000
        $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$this->po->id}/payments",
            [
                'amount' => 40000,
                'payment_date' => '2026-08-15',
                'payment_method' => 'e_wallet',
            ]
        )->assertStatus(201);

        $this->po->refresh();
        $this->assertEquals('paid', $this->po->payment_status);
    }

    public function test_full_payment_sets_status_paid(): void
    {
        $response = $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$this->po->id}/payments",
            [
                'amount' => 100000,
                'payment_date' => '2026-08-05',
                'payment_method' => 'bank_transfer',
            ]
        );

        $response->assertStatus(201);

        $this->po->refresh();
        $this->assertEquals('paid', $this->po->payment_status);
    }

    public function test_rejects_overpayment(): void
    {
        $response = $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$this->po->id}/payments",
            [
                'amount' => 150000,
                'payment_date' => '2026-08-05',
                'payment_method' => 'cash',
            ]
        );

        $response->assertStatus(422);

        // No payment should be recorded
        $this->assertDatabaseMissing('supplier_payments', [
            'purchase_order_id' => $this->po->id,
        ]);
    }

    public function test_cannot_pay_cancelled_po(): void
    {
        // Cancel the PO
        $this->po->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson(
            "/api/supplier/purchase-orders/{$this->po->id}/payments",
            [
                'amount' => 50000,
                'payment_date' => '2026-08-05',
                'payment_method' => 'cash',
            ]
        );

        $response->assertStatus(422);

        $this->assertDatabaseMissing('supplier_payments', [
            'purchase_order_id' => $this->po->id,
        ]);
    }
}
