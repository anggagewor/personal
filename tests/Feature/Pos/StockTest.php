<?php

namespace Tests\Feature\Pos;

use Modules\Pos\Infrastructure\Models\CategoryModel;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Pos\Infrastructure\Models\ProductModel;
use Modules\Pos\Infrastructure\Models\ProductVariantModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private OutletModel $outlet;
    private ProductModel $product;
    private ProductVariantModel $variant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->outlet = OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Test Outlet',
            'business_type' => 'retail',
        ]);
        $category = CategoryModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Test Category',
            'sort_order' => 0,
        ]);
        $this->product = ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'base_price' => 10000,
            'status' => 'active',
            'track_stock' => true,
        ]);
        $this->variant = ProductVariantModel::create([
            'product_id' => $this->product->id,
            'name' => 'Default',
            'price' => 10000,
            'stock_quantity' => 20,
        ]);
    }

    public function test_user_can_adjust_stock_restock(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/pos/products/{$this->product->id}/stock", [
            'product_variant_id' => $this->variant->id,
            'type' => 'restock',
            'quantity' => 10,
            'reason' => 'Restok dari supplier',
        ]);

        $response->assertStatus(201);

        // Verify stock updated
        $this->variant->refresh();
        $this->assertEquals(30, $this->variant->stock_quantity);

        // Verify adjustment logged
        $this->assertDatabaseHas('pos_stock_adjustments', [
            'product_variant_id' => $this->variant->id,
            'type' => 'restock',
            'quantity' => 10,
        ]);
    }

    public function test_user_can_adjust_stock_correction(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/pos/products/{$this->product->id}/stock", [
            'product_variant_id' => $this->variant->id,
            'type' => 'correction',
            'quantity' => 5,
            'reason' => 'Koreksi stok manual',
        ]);

        $response->assertStatus(201);

        // Correction type decrements stock: 20 - 5 = 15
        $this->variant->refresh();
        $this->assertEquals(15, $this->variant->stock_quantity);
    }

    public function test_zero_quantity_adjustment_rejected(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/pos/products/{$this->product->id}/stock", [
            'product_variant_id' => $this->variant->id,
            'type' => 'restock',
            'quantity' => 0,
            'reason' => 'Should fail',
        ]);

        $response->assertStatus(422);

        // Verify no adjustment logged
        $this->assertDatabaseMissing('pos_stock_adjustments', [
            'product_variant_id' => $this->variant->id,
            'quantity' => 0,
        ]);
    }

    public function test_user_can_view_stock_levels(): void
    {
        $response = $this->actingAs($this->user)->getJson("/api/pos/outlets/{$this->outlet->id}/stock");

        $response->assertOk();

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals($this->product->id, $data[0]['product_id']);
        $this->assertEquals($this->variant->id, $data[0]['variant_id']);
        $this->assertEquals(20, $data[0]['stock_quantity']);
    }

    public function test_stock_adjustment_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/pos/products/{$this->product->id}/stock", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_variant_id', 'type', 'quantity']);
    }

    public function test_stock_adjustment_validates_type(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/pos/products/{$this->product->id}/stock", [
            'product_variant_id' => $this->variant->id,
            'type' => 'invalid_type',
            'quantity' => 5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_cannot_adjust_stock_of_other_users_product(): void
    {
        $otherUser = User::factory()->create();
        $otherOutlet = OutletModel::create([
            'user_id' => $otherUser->id,
            'name' => 'Other Outlet',
            'business_type' => 'retail',
        ]);
        $category = CategoryModel::create([
            'outlet_id' => $otherOutlet->id,
            'name' => 'Other Category',
            'sort_order' => 0,
        ]);
        $otherProduct = ProductModel::create([
            'outlet_id' => $otherOutlet->id,
            'category_id' => $category->id,
            'name' => 'Other Product',
            'base_price' => 5000,
            'status' => 'active',
        ]);
        $otherVariant = ProductVariantModel::create([
            'product_id' => $otherProduct->id,
            'name' => 'Default',
            'price' => 5000,
            'stock_quantity' => 10,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/pos/products/{$otherProduct->id}/stock", [
            'product_variant_id' => $otherVariant->id,
            'type' => 'restock',
            'quantity' => 100,
        ]);

        $response->assertStatus(403);
    }

    public function test_stock_adjustment_for_nonexistent_product_returns_404(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/pos/products/9999/stock', [
            'product_variant_id' => $this->variant->id,
            'type' => 'restock',
            'quantity' => 10,
        ]);

        $response->assertStatus(404);
    }
}
