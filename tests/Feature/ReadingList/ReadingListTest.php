<?php

namespace Tests\Feature\ReadingList;

use Modules\ReadingList\Infrastructure\Models\ReadingItemModel as ReadingItem;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingListTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_reading_items(): void
    {
        ReadingItem::create(['user_id' => $this->user->id, 'title' => 'Article 1', 'url' => 'https://example.com/1']);
        ReadingItem::create(['user_id' => $this->user->id, 'title' => 'Article 2', 'url' => 'https://example.com/2']);

        $response = $this->actingAs($this->user)->getJson('/api/reading-list');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_filter_unread(): void
    {
        ReadingItem::create(['user_id' => $this->user->id, 'title' => 'Read', 'url' => 'https://a.com', 'is_read' => true]);
        ReadingItem::create(['user_id' => $this->user->id, 'title' => 'Unread', 'url' => 'https://b.com', 'is_read' => false]);

        $response = $this->actingAs($this->user)->getJson('/api/reading-list?is_read=0');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_filter_favorites(): void
    {
        ReadingItem::create(['user_id' => $this->user->id, 'title' => 'Fav', 'url' => 'https://a.com', 'is_favorite' => true]);
        ReadingItem::create(['user_id' => $this->user->id, 'title' => 'Normal', 'url' => 'https://b.com', 'is_favorite' => false]);

        $response = $this->actingAs($this->user)->getJson('/api/reading-list?is_favorite=1');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_store_reading_item(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/reading-list', [
            'url' => 'https://laravel.com/docs/11.x',
            'title' => 'Laravel Docs',
        ]);

        $response->assertStatus(201);
    }

    public function test_store_requires_url(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/reading-list', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    public function test_store_validates_url_format(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/reading-list', [
            'url' => 'not-a-url',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    public function test_user_can_toggle_read(): void
    {
        $item = ReadingItem::create(['user_id' => $this->user->id, 'title' => 'A', 'url' => 'https://a.com', 'is_read' => false]);

        $response = $this->actingAs($this->user)->postJson("/api/reading-list/{$item->id}/toggle-read");

        $response->assertOk();
        $this->assertTrue($item->fresh()->is_read);
    }

    public function test_user_can_toggle_favorite(): void
    {
        $item = ReadingItem::create(['user_id' => $this->user->id, 'title' => 'A', 'url' => 'https://a.com', 'is_favorite' => false]);

        $response = $this->actingAs($this->user)->postJson("/api/reading-list/{$item->id}/toggle-favorite");

        $response->assertOk();
        $this->assertTrue($item->fresh()->is_favorite);
    }

    public function test_user_cannot_toggle_other_users_item(): void
    {
        $otherUser = User::factory()->create();
        $item = ReadingItem::create(['user_id' => $otherUser->id, 'title' => 'A', 'url' => 'https://a.com']);

        $response = $this->actingAs($this->user)->postJson("/api/reading-list/{$item->id}/toggle-read");

        $response->assertStatus(403);
    }

    public function test_user_can_delete_reading_item(): void
    {
        $item = ReadingItem::create(['user_id' => $this->user->id, 'title' => 'A', 'url' => 'https://a.com']);

        $response = $this->actingAs($this->user)->deleteJson("/api/reading-list/{$item->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('reading_list', ['id' => $item->id]);
    }
}
