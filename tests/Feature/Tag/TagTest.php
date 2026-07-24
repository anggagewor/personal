<?php

namespace Tests\Feature\Tag;

use Modules\Note\Infrastructure\Models\NoteModel as Note;
use Modules\Tag\Infrastructure\Models\TagModel as Tag;
use Modules\Task\Infrastructure\Models\TaskModel as Task;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_tags(): void
    {
        Tag::create(['user_id' => $this->user->id, 'name' => 'Work', 'color' => 'blue']);
        Tag::create(['user_id' => $this->user->id, 'name' => 'Personal', 'color' => 'green']);

        $response = $this->actingAs($this->user)->getJson('/api/tags');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_tags_ordered_alphabetically(): void
    {
        Tag::create(['user_id' => $this->user->id, 'name' => 'Zebra']);
        Tag::create(['user_id' => $this->user->id, 'name' => 'Apple']);

        $response = $this->actingAs($this->user)->getJson('/api/tags');

        $data = $response->json('data');
        $this->assertEquals('Apple', $data[0]['name']);
    }

    public function test_user_can_create_tag(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/tags', [
            'name' => 'Important',
            'color' => 'red',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Important');
    }

    public function test_create_tag_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/tags', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_user_can_update_tag(): void
    {
        $tag = Tag::create(['user_id' => $this->user->id, 'name' => 'Old', 'color' => 'blue']);

        $response = $this->actingAs($this->user)->putJson("/api/tags/{$tag->id}", [
            'name' => 'Updated',
            'color' => 'purple',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated');
    }

    public function test_user_cannot_update_other_users_tag(): void
    {
        $otherUser = User::factory()->create();
        $tag = Tag::create(['user_id' => $otherUser->id, 'name' => 'Private']);

        $response = $this->actingAs($this->user)->putJson("/api/tags/{$tag->id}", [
            'name' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_tag(): void
    {
        $tag = Tag::create(['user_id' => $this->user->id, 'name' => 'Delete']);

        $response = $this->actingAs($this->user)->deleteJson("/api/tags/{$tag->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_user_can_attach_tag_to_note(): void
    {
        $tag = Tag::create(['user_id' => $this->user->id, 'name' => 'Work']);
        $note = Note::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson("/api/tags/{$tag->id}/attach", [
            'taggable_type' => 'note',
            'taggable_id' => $note->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag->id,
            'taggable_id' => $note->id,
            'taggable_type' => \Modules\Note\Infrastructure\Models\NoteModel::class,
        ]);
    }

    public function test_user_can_attach_tag_to_task(): void
    {
        $tag = Tag::create(['user_id' => $this->user->id, 'name' => 'Urgent']);
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson("/api/tags/{$tag->id}/attach", [
            'taggable_type' => 'task',
            'taggable_id' => $task->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag->id,
            'taggable_id' => $task->id,
            'taggable_type' => \Modules\Task\Infrastructure\Models\TaskModel::class,
        ]);
    }

    public function test_user_can_detach_tag(): void
    {
        $tag = Tag::create(['user_id' => $this->user->id, 'name' => 'Work']);
        $note = Note::factory()->create(['user_id' => $this->user->id]);
        $tag->notes()->attach($note->id);

        $response = $this->actingAs($this->user)->postJson("/api/tags/{$tag->id}/detach", [
            'taggable_type' => 'note',
            'taggable_id' => $note->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('taggables', [
            'tag_id' => $tag->id,
            'taggable_id' => $note->id,
            'taggable_type' => \Modules\Note\Infrastructure\Models\NoteModel::class,
        ]);
    }

    public function test_attach_validates_taggable_type(): void
    {
        $tag = Tag::create(['user_id' => $this->user->id, 'name' => 'Test']);

        $response = $this->actingAs($this->user)->postJson("/api/tags/{$tag->id}/attach", [
            'taggable_type' => 'invalid',
            'taggable_id' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['taggable_type']);
    }
}
