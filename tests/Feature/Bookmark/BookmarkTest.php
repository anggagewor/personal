<?php

namespace Tests\Feature\Bookmark;

use Modules\Bookmark\Infrastructure\Models\BookmarkModel as Bookmark;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookmarkTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_bookmarks(): void
    {
        Bookmark::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/bookmarks');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_bookmarks_are_grouped_by_category(): void
    {
        Bookmark::factory()->create(['user_id' => $this->user->id, 'category' => 'Work']);
        Bookmark::factory()->create(['user_id' => $this->user->id, 'category' => 'Personal']);

        $response = $this->actingAs($this->user)->getJson('/api/bookmarks');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertArrayHasKey('Work', $data);
        $this->assertArrayHasKey('Personal', $data);
    }

    public function test_user_cannot_see_other_users_bookmarks(): void
    {
        $otherUser = User::factory()->create();
        Bookmark::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->getJson('/api/bookmarks');

        $response->assertOk();
        $this->assertEmpty($response->json('data'));
    }

    public function test_user_can_create_bookmark(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/bookmarks', [
            'title' => 'Laravel Docs',
            'url' => 'https://laravel.com/docs',
            'category' => 'Dev',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Laravel Docs');

        $this->assertDatabaseHas('bookmarks', ['url' => 'https://laravel.com/docs']);
    }

    public function test_create_bookmark_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/bookmarks', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'url']);
    }

    public function test_user_can_update_bookmark(): void
    {
        $bookmark = Bookmark::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->putJson("/api/bookmarks/{$bookmark->id}", [
            'title' => 'Updated',
            'url' => 'https://updated.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated');
    }

    public function test_user_cannot_update_other_users_bookmark(): void
    {
        $otherUser = User::factory()->create();
        $bookmark = Bookmark::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->putJson("/api/bookmarks/{$bookmark->id}", [
            'title' => 'Hacked',
            'url' => 'https://evil.com',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_bookmark(): void
    {
        $bookmark = Bookmark::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/bookmarks/{$bookmark->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('bookmarks', ['id' => $bookmark->id]);
    }

    public function test_user_cannot_delete_other_users_bookmark(): void
    {
        $otherUser = User::factory()->create();
        $bookmark = Bookmark::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/bookmarks/{$bookmark->id}");

        $response->assertStatus(403);
    }
}
