<?php

namespace Tests\Feature\Habit;

use Modules\Habit\Infrastructure\Models\HabitModel as Habit;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HabitTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_active_habits(): void
    {
        Habit::create(['user_id' => $this->user->id, 'name' => 'Active', 'is_active' => true]);
        Habit::create(['user_id' => $this->user->id, 'name' => 'Inactive', 'is_active' => false]);

        $response = $this->actingAs($this->user)->getJson('/api/habits');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_create_habit(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/habits', [
            'name' => 'Exercise',
            'icon' => '🏃',
            'color' => 'green',
            'frequency' => 'daily',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Exercise');
    }

    public function test_create_habit_validates_frequency(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/habits', [
            'name' => 'Bad',
            'frequency' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['frequency']);
    }

    public function test_user_can_update_habit(): void
    {
        $habit = Habit::create(['user_id' => $this->user->id, 'name' => 'Old', 'is_active' => true]);

        $response = $this->actingAs($this->user)->putJson("/api/habits/{$habit->id}", [
            'name' => 'Updated',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated');
    }

    public function test_user_cannot_update_other_users_habit(): void
    {
        $otherUser = User::factory()->create();
        $habit = Habit::create(['user_id' => $otherUser->id, 'name' => 'Private', 'is_active' => true]);

        $response = $this->actingAs($this->user)->putJson("/api/habits/{$habit->id}", [
            'name' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_habit(): void
    {
        $habit = Habit::create(['user_id' => $this->user->id, 'name' => 'Delete', 'is_active' => true]);

        $response = $this->actingAs($this->user)->deleteJson("/api/habits/{$habit->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('habits', ['id' => $habit->id]);
    }

    public function test_user_can_toggle_habit_for_today(): void
    {
        $habit = Habit::create(['user_id' => $this->user->id, 'name' => 'Read', 'is_active' => true]);

        $response = $this->actingAs($this->user)->postJson("/api/habits/{$habit->id}/toggle");

        $response->assertOk();
        $this->assertNotNull($habit->fresh()->last_completed_at);
        $this->assertEquals(1, $habit->fresh()->current_streak);
    }

    public function test_user_can_toggle_habit_off(): void
    {
        $habit = Habit::create([
            'user_id' => $this->user->id,
            'name' => 'Read',
            'is_active' => true,
            'current_streak' => 1,
            'last_completed_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/habits/{$habit->id}/toggle");

        $response->assertOk();
        $this->assertNull($habit->fresh()->last_completed_at);
        $this->assertEquals(0, $habit->fresh()->current_streak);
    }

    public function test_toggle_returns_streak(): void
    {
        $habit = Habit::create([
            'user_id' => $this->user->id,
            'name' => 'Streak',
            'is_active' => true,
            'current_streak' => 5,
            'longest_streak' => 10,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/habits');

        $response->assertOk();
        $data = collect($response->json('data'))->firstWhere('id', $habit->id);
        $this->assertEquals(5, $data['currentStreak']);
    }

    public function test_user_cannot_toggle_other_users_habit(): void
    {
        $otherUser = User::factory()->create();
        $habit = Habit::create(['user_id' => $otherUser->id, 'name' => 'Private', 'is_active' => true]);

        $response = $this->actingAs($this->user)->postJson("/api/habits/{$habit->id}/toggle");

        $response->assertStatus(403);
    }
}
