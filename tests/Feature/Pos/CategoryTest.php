<?php

namespace Tests\Feature\Pos;

use Modules\Pos\Infrastructure\Models\CategoryModel;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Pos\Infrastructure\Models\ProductModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private OutletModel $outlet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->outlet = OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Test Outlet',
            'business_type' => 'retail',
        ]);
    }

    public function test_user_can_create_category(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/pos/outlets/{$this->outlet->id}/categories", [
            'name' => 'Makanan',
            'icon' => 'utensils',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Makanan');

        $this->assertDatabaseHas('pos_categories', [
            'outlet_id' => $this->outlet->id,
            'name' => 'Makanan',
            'icon' => 'utensils',
        ]);
    }

    public function test_create_category_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/pos/outlets/{$this->outlet->id}/categories", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_user_can_list_categories(): void
    {
        CategoryModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Makanan',
            'sort_order' => 0,
        ]);
        CategoryModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Minuman',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/pos/outlets/{$this->outlet->id}/categories");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_update_category(): void
    {
        $category = CategoryModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Old Name',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/pos/categories/{$category->id}", [
            'name' => 'New Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name');

        $this->assertDatabaseHas('pos_categories', [
            'id' => $category->id,
            'name' => 'New Name',
        ]);
    }

    public function test_duplicate_category_name_returns_409(): void
    {
        CategoryModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Makanan',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/pos/outlets/{$this->outlet->id}/categories", [
            'name' => 'Makanan',
        ]);

        $response->assertStatus(409);
    }

    public function test_update_to_duplicate_name_returns_409(): void
    {
        CategoryModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Makanan',
            'sort_order' => 0,
        ]);
        $category = CategoryModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Minuman',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/pos/categories/{$category->id}", [
            'name' => 'Makanan',
        ]);

        $response->assertStatus(409);
    }

    public function test_user_can_reorder_categories(): void
    {
        $cat1 = CategoryModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'First',
            'sort_order' => 0,
        ]);
        $cat2 = CategoryModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Second',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/pos/categories/reorder', [
            'ordered_ids' => [$cat2->id, $cat1->id],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('pos_categories', [
            'id' => $cat2->id,
            'sort_order' => 0,
        ]);
        $this->assertDatabaseHas('pos_categories', [
            'id' => $cat1->id,
            'sort_order' => 1,
        ]);
    }

    public function test_delete_category_reassigns_products_to_uncategorized(): void
    {
        $category = CategoryModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'To Delete',
            'sort_order' => 0,
        ]);

        $product = ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'base_price' => 10000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/pos/categories/{$category->id}");

        $response->assertOk();

        // Category should be deleted
        $this->assertDatabaseMissing('pos_categories', ['id' => $category->id]);

        // Product should be reassigned to "Uncategorized"
        $uncategorized = CategoryModel::where('outlet_id', $this->outlet->id)
            ->where('name', 'Uncategorized')
            ->first();

        $this->assertNotNull($uncategorized);
        $this->assertDatabaseHas('pos_products', [
            'id' => $product->id,
            'category_id' => $uncategorized->id,
        ]);
    }

    public function test_delete_nonexistent_category_returns_404(): void
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/pos/categories/9999');

        $response->assertStatus(404);
    }

    public function test_cannot_access_other_users_outlet_categories(): void
    {
        $otherUser = User::factory()->create();
        $otherOutlet = OutletModel::create([
            'user_id' => $otherUser->id,
            'name' => 'Other Outlet',
            'business_type' => 'retail',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/pos/outlets/{$otherOutlet->id}/categories");

        $response->assertStatus(403);
    }
}
