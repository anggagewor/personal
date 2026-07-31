<?php

namespace Tests\Feature\Pos;

use Modules\Pos\Infrastructure\Models\CategoryModel;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Pos\Infrastructure\Models\ProductModel;
use Modules\Pos\Infrastructure\Models\ProductVariantModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private OutletModel $outlet;
    private CategoryModel $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->outlet = OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Test Outlet',
            'business_type' => 'retail',
        ]);
        $this->category = CategoryModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Makanan',
            'sort_order' => 0,
        ]);
    }

    public function test_user_can_create_product(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/pos/outlets/{$this->outlet->id}/products", [
            'name' => 'Nasi Goreng',
            'base_price' => 15000,
            'category_id' => $this->category->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Nasi Goreng');

        $this->assertDatabaseHas('pos_products', [
            'outlet_id' => $this->outlet->id,
            'name' => 'Nasi Goreng',
            'base_price' => 15000,
        ]);
    }

    public function test_create_product_auto_generates_sku(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/pos/outlets/{$this->outlet->id}/products", [
            'name' => 'Nasi Goreng',
            'base_price' => 15000,
            'category_id' => $this->category->id,
        ]);

        $response->assertStatus(201);

        // Product should have a variant with auto-generated SKU
        $product = ProductModel::where('name', 'Nasi Goreng')->first();
        $variant = ProductVariantModel::where('product_id', $product->id)->first();
        $this->assertNotNull($variant);
        $this->assertNotNull($variant->sku);
    }

    public function test_create_product_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/pos/outlets/{$this->outlet->id}/products", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'base_price', 'category_id']);
    }

    public function test_create_product_with_variants(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/pos/outlets/{$this->outlet->id}/products", [
            'name' => 'Es Kopi',
            'base_price' => 12000,
            'category_id' => $this->category->id,
            'has_variants' => true,
            'variants' => [
                ['name' => 'Kecil', 'price' => 12000, 'stock_quantity' => 10],
                ['name' => 'Besar', 'price' => 18000, 'stock_quantity' => 5],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.has_variants', true);

        $product = ProductModel::where('name', 'Es Kopi')->first();
        $this->assertCount(2, ProductVariantModel::where('product_id', $product->id)->get());
    }

    public function test_duplicate_product_name_in_same_category_returns_409(): void
    {
        ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $this->category->id,
            'name' => 'Nasi Goreng',
            'base_price' => 15000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/pos/outlets/{$this->outlet->id}/products", [
            'name' => 'Nasi Goreng',
            'base_price' => 16000,
            'category_id' => $this->category->id,
        ]);

        $response->assertStatus(409);
    }

    public function test_user_can_view_product(): void
    {
        $product = ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $this->category->id,
            'name' => 'Test Product',
            'base_price' => 10000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/pos/products/{$product->id}");

        $response->assertOk()
            ->assertJsonPath('data.name', 'Test Product');
    }

    public function test_user_can_update_product(): void
    {
        $product = ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $this->category->id,
            'name' => 'Old Product',
            'base_price' => 10000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/pos/products/{$product->id}", [
            'name' => 'Updated Product',
            'base_price' => 12000,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Product');

        $this->assertDatabaseHas('pos_products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'base_price' => 12000,
        ]);
    }

    public function test_user_can_deactivate_product(): void
    {
        $product = ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $this->category->id,
            'name' => 'Active Product',
            'base_price' => 10000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/pos/products/{$product->id}/deactivate");

        $response->assertOk();

        $this->assertDatabaseHas('pos_products', [
            'id' => $product->id,
            'status' => 'inactive',
        ]);
    }

    public function test_user_can_search_products_by_name(): void
    {
        ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $this->category->id,
            'name' => 'Nasi Goreng Spesial',
            'base_price' => 20000,
            'status' => 'active',
        ]);
        ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $this->category->id,
            'name' => 'Mie Ayam',
            'base_price' => 15000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/pos/outlets/{$this->outlet->id}/products?search=Nasi");

        $response->assertOk();

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Nasi Goreng Spesial', $data[0]['name']);
    }

    public function test_user_can_list_products_by_outlet(): void
    {
        ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $this->category->id,
            'name' => 'Product 1',
            'base_price' => 10000,
            'status' => 'active',
        ]);
        ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $this->category->id,
            'name' => 'Product 2',
            'base_price' => 20000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/pos/outlets/{$this->outlet->id}/products");

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_cannot_view_product_from_other_users_outlet(): void
    {
        $otherUser = User::factory()->create();
        $otherOutlet = OutletModel::create([
            'user_id' => $otherUser->id,
            'name' => 'Other Outlet',
            'business_type' => 'retail',
        ]);
        $product = ProductModel::create([
            'outlet_id' => $otherOutlet->id,
            'category_id' => $this->category->id,
            'name' => 'Secret Product',
            'base_price' => 10000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/pos/products/{$product->id}");

        $response->assertStatus(403);
    }

    public function test_view_nonexistent_product_returns_404(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/pos/products/9999');

        $response->assertStatus(404);
    }
}
