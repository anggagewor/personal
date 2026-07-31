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

class VoidTransactionTest extends TestCase
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
            'name' => 'Warung Test',
            'business_type' => 'warung',
            'payment_flow' => 'pay_first',
        ]);
        $this->category = CategoryModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Makanan',
            'sort_order' => 0,
        ]);
        $this->product = ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $this->category->id,
            'name' => 'Nasi Goreng',
            'base_price' => 15000,
            'has_variants' => false,
            'track_stock' => true,
            'status' => 'active',
        ]);
        $this->variant = ProductVariantModel::create([
            'product_id' => $this->product->id,
            'name' => 'default',
            'price' => 15000,
            'stock_quantity' => 50,
        ]);
    }

    private function createCompletedTransaction(int $quantity = 2): TransactionModel
    {
        $transaction = TransactionModel::create([
            'outlet_id' => $this->outlet->id,
            'transaction_number' => 'TRX-' . now()->format('ymd') . '-0001',
            'subtotal' => $quantity * 15000,
            'discount_amount' => 0,
            'total' => $quantity * 15000,
            'payment_method' => 'Tunai',
            'payment_method_type' => 'cash',
            'amount_tendered' => $quantity * 15000 + 5000,
            'change_amount' => 5000,
            'status' => 'completed',
        ]);

        TransactionItemModel::create([
            'transaction_id' => $transaction->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'product_name' => 'Nasi Goreng',
            'variant_name' => null,
            'quantity' => $quantity,
            'unit_price' => 15000,
            'subtotal' => $quantity * 15000,
        ]);

        // Simulate stock decrement that happened during checkout
        $this->variant->decrement('stock_quantity', $quantity);

        return $transaction;
    }

    public function test_can_void_completed_transaction(): void
    {
        $transaction = $this->createCompletedTransaction();

        $response = $this->actingAs($this->user)->postJson("/api/pos/transactions/{$transaction->id}/void", [
            'reason' => 'Pesanan salah',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'voided')
            ->assertJsonPath('data.void_reason', 'Pesanan salah');

        $this->assertDatabaseHas('pos_transactions', [
            'id' => $transaction->id,
            'status' => 'voided',
            'void_reason' => 'Pesanan salah',
        ]);
    }

    public function test_void_restores_stock(): void
    {
        $transaction = $this->createCompletedTransaction(3);

        // Stock was 50, decreased by 3 → 47
        $this->assertEquals(47, $this->variant->fresh()->stock_quantity);

        $this->actingAs($this->user)->postJson("/api/pos/transactions/{$transaction->id}/void", [
            'reason' => 'Pelanggan batal',
        ]);

        // Stock should be restored: 47 + 3 = 50
        $this->assertEquals(50, $this->variant->fresh()->stock_quantity);
    }

    public function test_cannot_void_already_voided_transaction(): void
    {
        $transaction = $this->createCompletedTransaction();

        // Void once
        $this->actingAs($this->user)->postJson("/api/pos/transactions/{$transaction->id}/void", [
            'reason' => 'Pesanan salah',
        ]);

        // Try to void again
        $response = $this->actingAs($this->user)->postJson("/api/pos/transactions/{$transaction->id}/void", [
            'reason' => 'Pesanan salah lagi',
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_void_pending_transaction(): void
    {
        $transaction = TransactionModel::create([
            'outlet_id' => $this->outlet->id,
            'transaction_number' => 'TRX-' . now()->format('ymd') . '-0001',
            'subtotal' => 30000,
            'discount_amount' => 0,
            'total' => 30000,
            'status' => 'pending',
        ]);

        TransactionItemModel::create([
            'transaction_id' => $transaction->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'product_name' => 'Nasi Goreng',
            'variant_name' => null,
            'quantity' => 2,
            'unit_price' => 15000,
            'subtotal' => 30000,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/pos/transactions/{$transaction->id}/void", [
            'reason' => 'Batal',
        ]);

        $response->assertStatus(422);
    }

    public function test_void_requires_reason(): void
    {
        $transaction = $this->createCompletedTransaction();

        $response = $this->actingAs($this->user)->postJson("/api/pos/transactions/{$transaction->id}/void", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    }

    public function test_void_within_24h_succeeds(): void
    {
        $transaction = $this->createCompletedTransaction();

        // Transaction was just created (within 24h), should void fine
        $response = $this->actingAs($this->user)->postJson("/api/pos/transactions/{$transaction->id}/void", [
            'reason' => 'Pembeli batal',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'voided');
    }

    public function test_void_beyond_24h_still_allowed_with_reason(): void
    {
        $transaction = $this->createCompletedTransaction();

        // Backdate transaction to more than 24h ago
        $transaction->update(['created_at' => now()->subHours(25)]);

        // Per current implementation, void is still allowed with reason
        // (24h threshold only adds confirmation requirement on frontend)
        $response = $this->actingAs($this->user)->postJson("/api/pos/transactions/{$transaction->id}/void", [
            'reason' => 'Kembali dana',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'voided');
    }

    public function test_cannot_void_other_users_transaction(): void
    {
        $transaction = $this->createCompletedTransaction();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)->postJson("/api/pos/transactions/{$transaction->id}/void", [
            'reason' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_void_nonexistent_transaction_returns_404(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/pos/transactions/99999/void', [
            'reason' => 'Test',
        ]);

        $response->assertStatus(404);
    }
}
