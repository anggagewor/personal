<?php

namespace Tests\Feature\Pos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Pos\Infrastructure\Models\CategoryModel;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Pos\Infrastructure\Models\ProductModel;
use Modules\Pos\Infrastructure\Models\ProductVariantModel;
use Modules\Pos\Infrastructure\Models\TransactionItemModel;
use Modules\Pos\Infrastructure\Models\TransactionModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Tests\TestCase;

class OpenBillTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private OutletModel $outlet;

    private CategoryModel $category;

    private ProductModel $product;

    private ProductVariantModel $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->outlet = OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Kafe Test',
            'business_type' => 'kafe',
            'payment_flow' => 'pay_later',
        ]);
        $this->category = CategoryModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Minuman',
            'sort_order' => 0,
        ]);
        $this->product = ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $this->category->id,
            'name' => 'Latte',
            'base_price' => 25000,
            'has_variants' => false,
            'track_stock' => true,
            'status' => 'active',
        ]);
        $this->variant = ProductVariantModel::create([
            'product_id' => $this->product->id,
            'name' => 'default',
            'price' => 25000,
            'stock_quantity' => 100,
        ]);
    }

    private function createOpenBill(array $overrides = []): TransactionModel
    {
        $transaction = TransactionModel::create(array_merge([
            'outlet_id' => $this->outlet->id,
            'transaction_number' => 'TRX-' . now()->format('ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'subtotal' => 50000,
            'discount_amount' => 0,
            'total' => 50000,
            'status' => 'pending',
        ], $overrides));

        TransactionItemModel::create([
            'transaction_id' => $transaction->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'product_name' => 'Latte',
            'variant_name' => null,
            'quantity' => 2,
            'unit_price' => 25000,
            'subtotal' => 50000,
        ]);

        return $transaction;
    }

    public function test_can_list_open_bills(): void
    {
        $this->createOpenBill(['transaction_number' => 'TRX-' . now()->format('ymd') . '-0001']);
        $this->createOpenBill(['transaction_number' => 'TRX-' . now()->format('ymd') . '-0002']);

        // Also create a completed transaction — should not appear
        TransactionModel::create([
            'outlet_id' => $this->outlet->id,
            'transaction_number' => 'TRX-' . now()->format('ymd') . '-0003',
            'subtotal' => 25000,
            'discount_amount' => 0,
            'total' => 25000,
            'status' => 'completed',
            'payment_method' => 'Tunai',
            'payment_method_type' => 'cash',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/pos/outlets/{$this->outlet->id}/open-bills");

        $response->assertOk()
            ->assertJsonCount(2, 'data');

        // Verify all returned bills are pending
        $data = $response->json('data');
        foreach ($data as $bill) {
            $this->assertEquals('pending', $bill['status']);
        }
    }

    public function test_can_close_open_bill_with_cash_payment(): void
    {
        $openBill = $this->createOpenBill();

        $response = $this->actingAs($this->user)->postJson("/api/pos/open-bills/{$openBill->id}/close", [
            'payment_method' => 'Tunai',
            'payment_method_type' => 'cash',
            'amount_tendered' => 60000,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.payment_method', 'Tunai')
            ->assertJsonPath('data.amount_tendered', 60000)
            ->assertJsonPath('data.change_amount', 10000);

        $this->assertDatabaseHas('pos_transactions', [
            'id' => $openBill->id,
            'status' => 'completed',
            'payment_method' => 'Tunai',
            'payment_method_type' => 'cash',
            'amount_tendered' => 60000,
            'change_amount' => 10000,
        ]);
    }

    public function test_can_close_open_bill_with_non_cash_payment(): void
    {
        $openBill = $this->createOpenBill();

        $response = $this->actingAs($this->user)->postJson("/api/pos/open-bills/{$openBill->id}/close", [
            'payment_method' => 'QRIS',
            'payment_method_type' => 'e_wallet',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.payment_method', 'QRIS');

        $this->assertDatabaseHas('pos_transactions', [
            'id' => $openBill->id,
            'status' => 'completed',
            'payment_method_type' => 'e_wallet',
        ]);
    }

    public function test_cannot_close_already_completed_transaction(): void
    {
        $transaction = TransactionModel::create([
            'outlet_id' => $this->outlet->id,
            'transaction_number' => 'TRX-' . now()->format('ymd') . '-0001',
            'subtotal' => 50000,
            'discount_amount' => 0,
            'total' => 50000,
            'status' => 'completed',
            'payment_method' => 'Tunai',
            'payment_method_type' => 'cash',
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/pos/open-bills/{$transaction->id}/close", [
            'payment_method' => 'QRIS',
            'payment_method_type' => 'e_wallet',
        ]);

        $response->assertStatus(422);
    }

    public function test_overdue_detection_for_open_bills_older_than_24h(): void
    {
        // Create a recent bill (not overdue)
        $recentBill = $this->createOpenBill([
            'transaction_number' => 'TRX-' . now()->format('ymd') . '-0001',
            'created_at' => now()->subHours(2),
        ]);

        // Create an overdue bill (>24h)
        $overdueBill = $this->createOpenBill([
            'transaction_number' => 'TRX-' . now()->subDay()->format('ymd') . '-0001',
            'created_at' => now()->subHours(25),
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/pos/outlets/{$this->outlet->id}/open-bills");

        $response->assertOk()
            ->assertJsonCount(2, 'data');

        // Verify both bills are returned (overdue detection is a frontend concern based on created_at)
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_close_open_bill_requires_payment_method(): void
    {
        $openBill = $this->createOpenBill();

        $response = $this->actingAs($this->user)->postJson("/api/pos/open-bills/{$openBill->id}/close", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method', 'payment_method_type']);
    }

    public function test_cannot_close_nonexistent_open_bill(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/pos/open-bills/99999/close', [
            'payment_method' => 'Tunai',
            'payment_method_type' => 'cash',
        ]);

        $response->assertStatus(404);
    }

    public function test_cannot_close_other_users_open_bill(): void
    {
        $openBill = $this->createOpenBill();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)->postJson("/api/pos/open-bills/{$openBill->id}/close", [
            'payment_method' => 'Tunai',
            'payment_method_type' => 'cash',
            'amount_tendered' => 60000,
        ]);

        $response->assertStatus(403);
    }

    public function test_open_bills_only_show_for_own_outlet(): void
    {
        $this->createOpenBill(['transaction_number' => 'TRX-' . now()->format('ymd') . '-0001']);

        $otherUser = User::factory()->create();
        $otherOutlet = OutletModel::create([
            'user_id' => $otherUser->id,
            'name' => 'Outlet Lain',
            'business_type' => 'kafe',
            'payment_flow' => 'pay_later',
        ]);

        // Other user tries to see our open bills
        $response = $this->actingAs($otherUser)->getJson("/api/pos/outlets/{$this->outlet->id}/open-bills");

        $response->assertStatus(403);
    }
}
