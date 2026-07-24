<?php

namespace Tests\Feature\Scratchpad;

use Modules\Scratchpad\Infrastructure\Models\ScratchpadModel as Scratchpad;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScratchpadTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_scratchpads(): void
    {
        Scratchpad::create(['user_id' => $this->user->id, 'content' => 'A', 'position' => 0]);
        Scratchpad::create(['user_id' => $this->user->id, 'content' => 'B', 'position' => 1]);

        $response = $this->actingAs($this->user)->getJson('/api/scratchpads');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_scratchpads_ordered_by_position(): void
    {
        Scratchpad::create(['user_id' => $this->user->id, 'content' => 'Second', 'position' => 1]);
        Scratchpad::create(['user_id' => $this->user->id, 'content' => 'First', 'position' => 0]);

        $response = $this->actingAs($this->user)->getJson('/api/scratchpads');

        $data = $response->json('data');
        $this->assertEquals('First', $data[0]['content']);
    }

    public function test_user_can_create_scratchpad(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/scratchpads', [
            'content' => 'Quick note',
            'color' => 'yellow',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.content', 'Quick note')
            ->assertJsonPath('data.color', 'yellow');
    }

    public function test_user_can_update_scratchpad(): void
    {
        $pad = Scratchpad::create(['user_id' => $this->user->id, 'content' => 'Old', 'position' => 0]);

        $response = $this->actingAs($this->user)->putJson("/api/scratchpads/{$pad->id}", [
            'content' => 'Updated',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.content', 'Updated');
    }

    public function test_user_cannot_update_other_users_scratchpad(): void
    {
        $otherUser = User::factory()->create();
        $pad = Scratchpad::create(['user_id' => $otherUser->id, 'content' => 'Private', 'position' => 0]);

        $response = $this->actingAs($this->user)->putJson("/api/scratchpads/{$pad->id}", [
            'content' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_scratchpad(): void
    {
        $pad = Scratchpad::create(['user_id' => $this->user->id, 'content' => 'Delete me', 'position' => 0]);

        $response = $this->actingAs($this->user)->deleteJson("/api/scratchpads/{$pad->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('scratchpads', ['id' => $pad->id]);
    }

    public function test_user_cannot_delete_other_users_scratchpad(): void
    {
        $otherUser = User::factory()->create();
        $pad = Scratchpad::create(['user_id' => $otherUser->id, 'content' => 'Nope', 'position' => 0]);

        $response = $this->actingAs($this->user)->deleteJson("/api/scratchpads/{$pad->id}");

        $response->assertStatus(403);
    }
}
