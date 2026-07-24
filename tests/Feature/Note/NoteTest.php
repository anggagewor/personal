<?php

namespace Tests\Feature\Note;

use Modules\Note\Infrastructure\Models\NoteModel as Note;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_notes(): void
    {
        Note::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/notes');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_user_cannot_see_other_users_notes(): void
    {
        $otherUser = User::factory()->create();
        Note::factory()->count(2)->create(['user_id' => $otherUser->id]);
        Note::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/notes');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_search_notes(): void
    {
        Note::factory()->create(['user_id' => $this->user->id, 'title' => 'Laravel Tips']);
        Note::factory()->create(['user_id' => $this->user->id, 'title' => 'Vue Guide']);

        $response = $this->actingAs($this->user)->getJson('/api/notes?search=Laravel');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_create_note(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/notes', [
            'title' => 'My Note',
            'content' => 'Some content here.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'My Note');

        $this->assertDatabaseHas('notes', ['title' => 'My Note', 'user_id' => $this->user->id]);
    }

    public function test_create_note_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/notes', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_user_can_show_own_note(): void
    {
        $note = Note::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson("/api/notes/{$note->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $note->id);
    }

    public function test_user_cannot_show_other_users_note(): void
    {
        $otherUser = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->getJson("/api/notes/{$note->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_update_note(): void
    {
        $note = Note::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->putJson("/api/notes/{$note->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated Title');
    }

    public function test_user_cannot_update_other_users_note(): void
    {
        $otherUser = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->putJson("/api/notes/{$note->id}", [
            'title' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_note(): void
    {
        $note = Note::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/notes/{$note->id}");

        $response->assertOk();
        $this->assertSoftDeleted('notes', ['id' => $note->id]);
    }

    public function test_user_cannot_delete_other_users_note(): void
    {
        $otherUser = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/notes/{$note->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_toggle_pin(): void
    {
        $note = Note::factory()->create(['user_id' => $this->user->id, 'is_pinned' => false]);

        $response = $this->actingAs($this->user)->postJson("/api/notes/{$note->id}/toggle-pin");

        $response->assertOk();
        $this->assertTrue($note->fresh()->is_pinned);
    }

    public function test_pinned_notes_appear_first(): void
    {
        Note::factory()->create(['user_id' => $this->user->id, 'is_pinned' => false, 'title' => 'Not Pinned']);
        Note::factory()->create(['user_id' => $this->user->id, 'is_pinned' => true, 'title' => 'Pinned']);

        $response = $this->actingAs($this->user)->getJson('/api/notes');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals('Pinned', $data[0]['title']);
    }
}
