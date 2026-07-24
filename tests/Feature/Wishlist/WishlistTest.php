<?php

namespace Tests\Feature\Wishlist;

use Modules\User\Infrastructure\Models\UserModel as User;
use Modules\Wishlist\Infrastructure\Models\WishlistItemModel as WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_wishlists(): void
    {
        WishlistItem::create(['user_id' => $this->user->id, 'title' => 'Item 1']);
        WishlistItem::create(['user_id' => $this->user->id, 'title' => 'Item 2']);

        $response = $this->actingAs($this->user)->getJson('/api/wishlists');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_filter_by_completed(): void
    {
        WishlistItem::create(['user_id' => $this->user->id, 'title' => 'Done', 'is_completed' => true]);
        WishlistItem::create(['user_id' => $this->user->id, 'title' => 'Not yet', 'is_completed' => false]);

        $response = $this->actingAs($this->user)->getJson('/api/wishlists?is_completed=true');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_filter_by_category(): void
    {
        WishlistItem::create(['user_id' => $this->user->id, 'title' => 'A', 'category' => 'tech']);
        WishlistItem::create(['user_id' => $this->user->id, 'title' => 'B', 'category' => 'books']);

        $response = $this->actingAs($this->user)->getJson('/api/wishlists?category=tech');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_create_wishlist_item(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/wishlists', [
            'title' => 'New MacBook',
            'description' => 'M4 Pro',
            'category' => 'tech',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'New MacBook');
    }

    public function test_create_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/wishlists', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_user_can_update_wishlist_item(): void
    {
        $item = WishlistItem::create(['user_id' => $this->user->id, 'title' => 'Old']);

        $response = $this->actingAs($this->user)->putJson("/api/wishlists/{$item->id}", [
            'title' => 'Updated',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated');
    }

    public function test_user_cannot_update_other_users_wishlist(): void
    {
        $otherUser = User::factory()->create();
        $item = WishlistItem::create(['user_id' => $otherUser->id, 'title' => 'Private']);

        $response = $this->actingAs($this->user)->putJson("/api/wishlists/{$item->id}", [
            'title' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_wishlist_item(): void
    {
        $item = WishlistItem::create(['user_id' => $this->user->id, 'title' => 'Delete me']);

        $response = $this->actingAs($this->user)->deleteJson("/api/wishlists/{$item->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('wishlists', ['id' => $item->id]);
    }

    public function test_user_can_toggle_completion(): void
    {
        $item = WishlistItem::create(['user_id' => $this->user->id, 'title' => 'Toggle me', 'is_completed' => false]);

        $response = $this->actingAs($this->user)->postJson("/api/wishlists/{$item->id}/toggle");

        $response->assertOk();
        $this->assertTrue($item->fresh()->is_completed);
        $this->assertNotNull($item->fresh()->completed_at);
    }

    public function test_toggle_removes_completed_at_when_uncompleted(): void
    {
        $item = WishlistItem::create(['user_id' => $this->user->id, 'title' => 'Done', 'is_completed' => true, 'completed_at' => now()]);

        $response = $this->actingAs($this->user)->postJson("/api/wishlists/{$item->id}/toggle");

        $response->assertOk();
        $this->assertFalse($item->fresh()->is_completed);
        $this->assertNull($item->fresh()->completed_at);
    }
}
