<?php

namespace Tests\Feature\Pos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Pos\Infrastructure\Models\CategoryModel;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Pos\Infrastructure\Models\ProductModel;
use Modules\Pos\Infrastructure\Models\ProductVariantModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private CategoryModel $category;

    private ProductModel $product;

    private ProductVariantModel $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    private function createOutlet(string $paymentFlow): OutletModel
    {
        return OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Outlet ' . $paymentFlow,
            'business_type' => 'kafe',
            'payment_flow' => $paymentFlow,
        ]);
    }

    private function createProductWithStock(OutletModel $outlet, int $stock = 100): array
    {
        $category = CategoryModel::create([
            'outlet_id' => $outlet->id,
            'name' => 'Minuman',
            'sort_order' => 0,
        ]);

        $product = ProductModel::create([
            'outlet_id' => $outlet->id,
            'category_id' => $category->id,
            'name' => 'Es Kopi',
            'base_price' => 20000,
            'has_variants' => false,
            'track_stock' => true,
            'status' => 'active',
        ]);

        $variant = ProductVariantModel::create([
            'product_id' => $product->id,
            'name' => 'default',
            'price' => 20000,
            'stock_quantity' => $stock,
        ]);

        return [$category, $product, $variant];
    }

    public function test_pay_first_outlet_creates_completed_transaction_with_payment(): void
    {
        $outlet = $this->createOutlet('pay_first');
        [$category, $product, $variant] = $this->createProductWithStock($outlet);

        $response = $this->actingAs($this->user)->postJson("/api/pos/outlets/{$outlet->id}/transactions", [
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => 1,
                    'unit_price' => 20000,
                    'product_name' => 'Es Kopi',
                ],
            ],
            'payment_method' => 'Tunai',
            'payment_method_type' => 'cash',
            'amount_tendered' => 20000,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.payment_method', 'Tunai');
    }

    public function test_pay_later_outlet_creates_pending_open_bill(): void
    {
        $outlet = $this->createOutlet('pay_later');
        [$category, $product, $variant] = $this->createProductWithStock($outlet);

        $response = $this->actingAs($this->user)->postJson("/api/pos/outlets/{$outlet->id}/transactions", [
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => 2,
                    'unit_price' => 20000,
                    'product_name' => 'Es Kopi',
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('pos_transactions', [
            'outlet_id' => $outlet->id,
            'status' => 'pending',
            'payment_method' => null,
        ]);
    }

    public function test_both_mode_with_payment_creates_completed(): void
    {
        $outlet = $this->createOutlet('both');
        [$category, $product, $variant] = $this->createProductWithStock($outlet);

        $response = $this->actingAs($this->user)->postJson("/api/pos/outlets/{$outlet->id}/transactions", [
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => 1,
                    'unit_price' => 20000,
                    'product_name' => 'Es Kopi',
                ],
            ],
            'payment_method' => 'QRIS',
            'payment_method_type' => 'e_wallet',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.payment_method', 'QRIS');
    }

    public function test_both_mode_without_payment_creates_pending(): void
    {
        $outlet = $this->createOutlet('both');
        [$category, $product, $variant] = $this->createProductWithStock($outlet);

        $response = $this->actingAs($this->user)->postJson("/api/pos/outlets/{$outlet->id}/transactions", [
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => 1,
                    'unit_price' => 20000,
                    'product_name' => 'Es Kopi',
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_pay_later_still_decrements_stock(): void
    {
        $outlet = $this->createOutlet('pay_later');
        [$category, $product, $variant] = $this->createProductWithStock($outlet, 10);

        $this->actingAs($this->user)->postJson("/api/pos/outlets/{$outlet->id}/transactions", [
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => 3,
                    'unit_price' => 20000,
                    'product_name' => 'Es Kopi',
                ],
            ],
        ]);

        $this->assertEquals(7, $variant->fresh()->stock_quantity);
    }

    public function test_pay_first_with_bank_transfer_creates_completed(): void
    {
        $outlet = $this->createOutlet('pay_first');
        [$category, $product, $variant] = $this->createProductWithStock($outlet);

        $response = $this->actingAs($this->user)->postJson("/api/pos/outlets/{$outlet->id}/transactions", [
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => 1,
                    'unit_price' => 20000,
                    'product_name' => 'Es Kopi',
                ],
            ],
            'payment_method' => 'BCA',
            'payment_method_type' => 'bank_transfer',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.payment_method', 'BCA');
    }
}
