<?php

namespace Tests\Feature\Pomodoro;

use Modules\Pomodoro\Infrastructure\Models\PomodoroModel as Pomodoro;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PomodoroTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_pomodoros(): void
    {
        Pomodoro::create(['user_id' => $this->user->id, 'duration' => 25, 'status' => 'completed', 'started_at' => now()]);
        Pomodoro::create(['user_id' => $this->user->id, 'duration' => 25, 'status' => 'running', 'started_at' => now()]);

        $response = $this->actingAs($this->user)->getJson('/api/pomodoros');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_user_can_start_pomodoro(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/pomodoros', [
            'duration' => 25,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'running')
            ->assertJsonPath('data.duration', 25);
    }

    public function test_user_can_start_pomodoro_with_task(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/pomodoros', [
            'task_id' => 99,
            'duration' => 30,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.taskId', 99);
    }

    public function test_user_can_complete_pomodoro(): void
    {
        $pomodoro = Pomodoro::create([
            'user_id' => $this->user->id,
            'duration' => 25,
            'status' => 'running',
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/pomodoros/{$pomodoro->id}/complete");

        $response->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->assertNotNull($pomodoro->fresh()->finished_at);
    }

    public function test_user_can_cancel_pomodoro(): void
    {
        $pomodoro = Pomodoro::create([
            'user_id' => $this->user->id,
            'duration' => 25,
            'status' => 'running',
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/pomodoros/{$pomodoro->id}/cancel");

        $response->assertOk()
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_user_cannot_complete_other_users_pomodoro(): void
    {
        $otherUser = User::factory()->create();
        $pomodoro = Pomodoro::create([
            'user_id' => $otherUser->id,
            'duration' => 25,
            'status' => 'running',
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/pomodoros/{$pomodoro->id}/complete");

        $response->assertStatus(403);
    }

    public function test_user_can_get_stats(): void
    {
        Pomodoro::create([
            'user_id' => $this->user->id,
            'duration' => 25,
            'status' => 'completed',
            'started_at' => now(),
            'finished_at' => now()->addMinutes(25),
        ]);
        Pomodoro::create([
            'user_id' => $this->user->id,
            'duration' => 30,
            'status' => 'completed',
            'started_at' => now(),
            'finished_at' => now()->addMinutes(30),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/pomodoros/stats');

        $response->assertOk()
            ->assertJsonPath('data.today', 2)
            ->assertJsonPath('data.total_minutes', 55);
    }
}
