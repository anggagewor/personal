<?php

namespace Tests\Feature\Pos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Pos\Infrastructure\Models\CategoryModel;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Pos\Infrastructure\Models\ProductModel;
use Modules\Pos\Infrastructure\Models\ProductVariantModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Tests\TestCase;

class TransactionTest extends TestCase
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

    public function test_can_complete_checkout_with_items(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/pos/outlets/{$this->outlet->id}/transactions", [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 2,
                    'unit_price' => 15000,
                    'product_name' => 'Nasi Goreng',
                ],
            ],
            'payment_method' => 'Tunai',
            'payment_method_type' => 'cash',
            'amount_tendered' => 50000,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.subtotal', 30000)
            ->assertJsonPath('data.total', 30000);

        $this->assertDatabaseHas('pos_transactions', [
            'outlet_id' => $this->outlet->id,
            'status' => 'completed',
            'subtotal' => 30000,
            'total' => 30000,
        ]);

        $this->assertDatabaseHas('pos_transaction_items', [
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
            'unit_price' => 15000,
            'subtotal' => 30000,
        ]);
    }

    public function test_checkout_decrements_stock(): void
    {
        $this->actingAs($this->user)->postJson("/api/pos/outlets/{$this->outlet->id}/transactions", [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 3,
                    'unit_price' => 15000,
                    'product_name' => 'Nasi Goreng',
                ],
            ],
            'payment_method' => 'Tunai',
            'payment_method_type' => 'cash',
            'amount_tendered' => 50000,
        ]);

        $this->assertEquals(47, $this->variant->fresh()->stock_quantity);
    }

    public function test_checkout_rejects_when_stock_insufficient(): void
    {
        $this->variant->update(['stock_quantity' => 2]);

        $response = $this->actingAs($this->user)->postJson("/api/pos/outlets/{$this->outlet->id}/transactions", [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 5,
                    'unit_price' => 15000,
                    'product_name' => 'Nasi Goreng',
                ],
            ],
            'payment_method' => 'Tunai',
            'payment_method_type' => 'cash',
            'amount_tendered' => 100000,
        ]);

        $response->assertStatus(422);
        $this->assertEquals(2, $this->variant->fresh()->stock_quantity);
    }

    public function test_transaction_number_is_generated_uniquely(): void
    {
        // Create first transaction
        $this->actingAs($this->user)->postJson("/api/pos/outlets/{$this->outlet->id}/transactions", [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 1,
                    'unit_price' => 15000,
                    'product_name' => 'Nasi Goreng',
                ],
            ],
            'payment_method' => 'Tunai',
            'payment_method_type' => 'cash',
            'amount_tendered' => 20000,
        ]);

        // Create second transaction
        $this->actingAs($this->user)->postJson("/api/pos/outlets/{$this->outlet->id}/transactions", [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 1,
                    'unit_price' => 15000,
                    'product_name' => 'Nasi Goreng',
                ],
            ],
            'payment_method' => 'Tunai',
            'payment_method_type' => 'cash',
            'amount_tendered' => 20000,
        ]);

        $transactions = \Modules\Pos\Infrastructure\Models\TransactionModel::where('outlet_id', $this->outlet->id)->get();

        $this->assertCount(2, $transactions);
        $this->assertNotEquals(
            $transactions[0]->transaction_number,
            $transactions[1]->transaction_number
        );

        // Verify format TRX-{YYMMDD}-{SEQ}
        $this->assertMatchesRegularExpression('/^TRX-\d{6}-\d{4}$/', $transactions[0]->transaction_number);
        $this->assertMatchesRegularExpression('/^TRX-\d{6}-\d{4}$/', $transactions[1]->transaction_number);
    }

    public function test_checkout_calculates_total_correctly_with_multiple_items(): void
    {
        $product2 = ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $this->category->id,
            'name' => 'Es Teh',
            'base_price' => 5000,
            'has_variants' => false,
            'track_stock' => true,
            'status' => 'active',
        ]);
        $variant2 = ProductVariantModel::create([
            'product_id' => $product2->id,
            'name' => 'default',
            'price' => 5000,
            'stock_quantity' => 100,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/pos/outlets/{$this->outlet->id}/transactions", [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 2,
                    'unit_price' => 15000,
                    'product_name' => 'Nasi Goreng',
                ],
                [
                    'product_id' => $product2->id,
                    'product_variant_id' => $variant2->id,
                    'quantity' => 3,
                    'unit_price' => 5000,
                    'product_name' => 'Es Teh',
                ],
            ],
            'payment_method' => 'Tunai',
            'payment_method_type' => 'cash',
            'amount_tendered' => 50000,
        ]);

        // 2*15000 + 3*5000 = 45000
        $response->assertStatus(201)
            ->assertJsonPath('data.subtotal', 45000)
            ->assertJsonPath('data.total', 45000);
    }

    public function test_checkout_requires_items(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/pos/outlets/{$this->outlet->id}/transactions", [
            'items' => [],
            'payment_method' => 'Tunai',
            'payment_method_type' => 'cash',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_checkout_with_cash_calculates_change(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/pos/outlets/{$this->outlet->id}/transactions", [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 1,
                    'unit_price' => 15000,
                    'product_name' => 'Nasi Goreng',
                ],
            ],
            'payment_method' => 'Tunai',
            'payment_method_type' => 'cash',
            'amount_tendered' => 20000,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.amount_tendered', 20000)
            ->assertJsonPath('data.change_amount', 5000);
    }

    public function test_unauthenticated_user_cannot_create_transaction(): void
    {
        $response = $this->postJson("/api/pos/outlets/{$this->outlet->id}/transactions", [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 1,
                    'unit_price' => 15000,
                    'product_name' => 'Nasi Goreng',
                ],
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_cannot_create_transaction_for_other_users_outlet(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)->postJson("/api/pos/outlets/{$this->outlet->id}/transactions", [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 1,
                    'unit_price' => 15000,
                    'product_name' => 'Nasi Goreng',
                ],
            ],
            'payment_method' => 'Tunai',
            'payment_method_type' => 'cash',
            'amount_tendered' => 20000,
        ]);

        $response->assertStatus(403);
    }
}
