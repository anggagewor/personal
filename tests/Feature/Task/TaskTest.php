<?php

namespace Tests\Feature\Task;

use Modules\Task\Infrastructure\Models\TaskModel as Task;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_tasks(): void
    {
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/tasks');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_user_cannot_see_other_users_tasks(): void
    {
        $otherUser = User::factory()->create();
        Task::factory()->count(2)->create(['user_id' => $otherUser->id]);
        Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/tasks');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_filter_tasks_by_status(): void
    {
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'completed']);

        $response = $this->actingAs($this->user)->getJson('/api/tasks?status=pending');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_filter_tasks_by_priority(): void
    {
        Task::factory()->create(['user_id' => $this->user->id, 'priority' => 'high']);
        Task::factory()->create(['user_id' => $this->user->id, 'priority' => 'low']);

        $response = $this->actingAs($this->user)->getJson('/api/tasks?priority=high');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_create_task(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/tasks', [
            'title' => 'New Task',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'New Task');

        $this->assertDatabaseHas('tasks', ['title' => 'New Task', 'user_id' => $this->user->id]);
    }

    public function test_create_task_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/tasks', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_user_can_update_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);

        $response = $this->actingAs($this->user)->putJson("/api/tasks/{$task->id}", [
            'status' => 'completed',
        ]);

        $response->assertOk();
        $this->assertEquals('completed', $task->fresh()->status);
    }

    public function test_user_cannot_update_other_users_task(): void
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->putJson("/api/tasks/{$task->id}", [
            'title' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/tasks/{$task->id}");

        $response->assertOk();
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_user_can_reorder_tasks(): void
    {
        $t1 = Task::factory()->create(['user_id' => $this->user->id, 'position' => 0]);
        $t2 = Task::factory()->create(['user_id' => $this->user->id, 'position' => 1]);
        $t3 = Task::factory()->create(['user_id' => $this->user->id, 'position' => 2]);

        $response = $this->actingAs($this->user)->postJson('/api/tasks/reorder', [
            'ordered_ids' => [$t3->id, $t1->id, $t2->id],
        ]);

        $response->assertOk();
        $this->assertEquals(0, $t3->fresh()->position);
        $this->assertEquals(1, $t1->fresh()->position);
        $this->assertEquals(2, $t2->fresh()->position);
    }
}
