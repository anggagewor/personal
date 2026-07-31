<?php

namespace Tests\Feature\Pos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Pos\Infrastructure\Models\CategoryModel;
use Modules\Pos\Infrastructure\Models\OrderQueueModel;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Pos\Infrastructure\Models\ProductModel;
use Modules\Pos\Infrastructure\Models\ProductVariantModel;
use Modules\Pos\Infrastructure\Models\TableModel;
use Modules\Pos\Infrastructure\Models\TableSessionModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Tests\TestCase;

class QrOrderPublicTest extends TestCase
{
    use RefreshDatabase;

    private OutletModel $outlet;

    private TableModel $table;

    private ProductModel $product;

    private ProductVariantModel $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();

        $this->outlet = OutletModel::create([
            'user_id' => $user->id,
            'name' => 'Warkop Test',
            'business_type' => 'warkop',
            'payment_flow' => 'pay_later',
        ]);

        $this->table = TableModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Meja 1',
            'token' => 'valid-qr-token-abc123',
            'is_active' => true,
        ]);

        $category = CategoryModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Minuman',
            'sort_order' => 0,
        ]);

        $this->product = ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $category->id,
            'name' => 'Kopi Susu',
            'base_price' => 15000,
            'status' => 'active',
            'has_variants' => false,
            'track_stock' => false,
        ]);

        $this->variant = ProductVariantModel::create([
            'product_id' => $this->product->id,
            'name' => 'Default',
            'price' => 15000,
            'stock_quantity' => 0,
        ]);
    }

    public function test_get_menu_by_token_no_auth(): void
    {
        $response = $this->getJson("/api/pos/qr/{$this->table->token}/menu");

        $response->assertOk()
            ->assertJsonPath('data.table_name', 'Meja 1')
            ->assertJsonStructure([
                'data' => [
                    'table_name',
                    'products',
                ],
                'message',
            ]);
    }

    public function test_submit_order_no_auth(): void
    {
        $response = $this->postJson("/api/pos/qr/{$this->table->token}/order", [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'variant_id' => $this->variant->id,
                    'quantity' => 2,
                    'name' => 'Kopi Susu',
                    'variant_name' => 'Default',
                    'price' => 15000,
                ],
            ],
            'notes' => 'Kurang gula',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'status'],
                'message',
            ]);

        $this->assertDatabaseHas('pos_order_queue', [
            'status' => 'pending',
            'notes' => 'Kurang gula',
        ]);
    }

    public function test_get_order_status_no_auth(): void
    {
        $session = TableSessionModel::create([
            'table_id' => $this->table->id,
            'status' => 'active',
            'started_at' => now(),
        ]);

        $order = OrderQueueModel::create([
            'table_session_id' => $session->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1, 'name' => 'Kopi Susu', 'price' => 15000],
            ],
            'status' => 'pending',
        ]);

        $response = $this->getJson("/api/pos/qr/{$this->table->token}/order/{$order->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_invalid_token_returns_404(): void
    {
        $response = $this->getJson('/api/pos/qr/invalid-token-xyz/menu');

        $response->assertStatus(404);
    }

    public function test_submit_order_with_invalid_token_returns_404(): void
    {
        $response = $this->postJson('/api/pos/qr/invalid-token-xyz/order', [
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 1,
                    'name' => 'Test',
                    'price' => 10000,
                ],
            ],
        ]);

        $response->assertStatus(404);
    }
}
