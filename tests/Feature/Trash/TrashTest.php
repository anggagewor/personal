<?php

namespace Tests\Feature\Trash;

use Modules\Note\Infrastructure\Models\NoteModel as Note;
use Modules\Task\Infrastructure\Models\TaskModel as Task;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrashTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_trashed_items(): void
    {
        $note = Note::factory()->create(['user_id' => $this->user->id]);
        $note->delete();

        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $task->delete();

        $response = $this->actingAs($this->user)->getJson('/api/trash');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_cannot_see_other_users_trash(): void
    {
        $otherUser = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $otherUser->id]);
        $note->delete();

        $response = $this->actingAs($this->user)->getJson('/api/trash');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_user_can_restore_note(): void
    {
        $note = Note::factory()->create(['user_id' => $this->user->id]);
        $note->delete();

        $response = $this->actingAs($this->user)->postJson("/api/trash/note/{$note->id}/restore");

        $response->assertOk();
        $this->assertDatabaseHas('notes', ['id' => $note->id, 'deleted_at' => null]);
    }

    public function test_user_can_restore_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $task->delete();

        $response = $this->actingAs($this->user)->postJson("/api/trash/task/{$task->id}/restore");

        $response->assertOk();
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'deleted_at' => null]);
    }

    public function test_user_cannot_restore_other_users_item(): void
    {
        $otherUser = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $otherUser->id]);
        $note->delete();

        $response = $this->actingAs($this->user)->postJson("/api/trash/note/{$note->id}/restore");

        $response->assertStatus(404);
    }

    public function test_user_can_force_delete_note(): void
    {
        $note = Note::factory()->create(['user_id' => $this->user->id]);
        $note->delete();

        $response = $this->actingAs($this->user)->deleteJson("/api/trash/note/{$note->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    public function test_user_cannot_force_delete_other_users_item(): void
    {
        $otherUser = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $otherUser->id]);
        $note->delete();

        $response = $this->actingAs($this->user)->deleteJson("/api/trash/note/{$note->id}");

        $response->assertStatus(404);
    }

    public function test_invalid_type_returns_404(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/trash/invalid/1/restore');

        $response->assertStatus(404);
    }

    public function test_trash_only_shows_items_within_30_days(): void
    {
        $recentNote = Note::factory()->create(['user_id' => $this->user->id]);
        $recentNote->delete();

        $oldNote = Note::factory()->create(['user_id' => $this->user->id]);
        $oldNote->delete();
        // Manually set deleted_at to 31 days ago
        Note::withTrashed()->where('id', $oldNote->id)->update(['deleted_at' => now()->subDays(31)]);

        $response = $this->actingAs($this->user)->getJson('/api/trash');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
