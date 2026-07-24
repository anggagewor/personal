<?php

namespace Tests\Feature\Goal;

use Modules\Goal\Infrastructure\Models\GoalModel as Goal;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_goals(): void
    {
        Goal::create(['user_id' => $this->user->id, 'title' => 'Goal 1', 'status' => 'active', 'progress' => 0]);
        Goal::create(['user_id' => $this->user->id, 'title' => 'Goal 2', 'status' => 'active', 'progress' => 50]);

        $response = $this->actingAs($this->user)->getJson('/api/goals');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_goals_include_milestones(): void
    {
        Goal::create([
            'user_id' => $this->user->id,
            'title' => 'Goal',
            'status' => 'active',
            'progress' => 0,
            'milestones' => [['id' => 1, 'title' => 'Step 1', 'completed' => false]],
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/goals');

        $response->assertOk();
        $this->assertNotEmpty($response->json('data.0.milestones'));
    }

    public function test_user_can_create_goal(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/goals', [
            'title' => 'Learn Rust',
            'target_date' => '2026-12-31',
            'milestones' => [
                ['title' => 'Read the book'],
                ['title' => 'Build a project'],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Learn Rust');

        $this->assertCount(2, $response->json('data.milestones'));
    }

    public function test_create_goal_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/goals', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_user_can_update_goal(): void
    {
        $goal = Goal::create(['user_id' => $this->user->id, 'title' => 'Old', 'status' => 'active', 'progress' => 0]);

        $response = $this->actingAs($this->user)->putJson("/api/goals/{$goal->id}", [
            'title' => 'Updated Goal',
            'status' => 'completed',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated Goal')
            ->assertJsonPath('data.status', 'completed');
    }

    public function test_user_cannot_update_other_users_goal(): void
    {
        $otherUser = User::factory()->create();
        $goal = Goal::create(['user_id' => $otherUser->id, 'title' => 'Private', 'status' => 'active', 'progress' => 0]);

        $response = $this->actingAs($this->user)->putJson("/api/goals/{$goal->id}", [
            'title' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_goal(): void
    {
        $goal = Goal::create(['user_id' => $this->user->id, 'title' => 'Delete me', 'status' => 'active', 'progress' => 0]);

        $response = $this->actingAs($this->user)->deleteJson("/api/goals/{$goal->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('goals', ['id' => $goal->id]);
    }

    public function test_user_can_add_milestone(): void
    {
        $goal = Goal::create(['user_id' => $this->user->id, 'title' => 'Goal', 'status' => 'active', 'progress' => 0]);

        $response = $this->actingAs($this->user)->postJson("/api/goals/{$goal->id}/milestones", [
            'title' => 'New Milestone',
        ]);

        $response->assertStatus(201);
        $milestones = $response->json('data.milestones');
        $this->assertCount(1, $milestones);
        $this->assertEquals('New Milestone', $milestones[0]['title']);
    }

    public function test_user_can_toggle_milestone(): void
    {
        $goal = Goal::create([
            'user_id' => $this->user->id,
            'title' => 'Goal',
            'status' => 'active',
            'progress' => 0,
            'milestones' => [
                ['id' => 1, 'title' => 'Step 1', 'completed' => false],
                ['id' => 2, 'title' => 'Step 2', 'completed' => false],
            ],
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/goals/{$goal->id}/milestones/1/toggle");

        $response->assertOk();
        $milestones = $response->json('data.milestones');
        $this->assertTrue($milestones[0]['completed']);
        $this->assertEquals(50, $response->json('data.progress')); // 1 of 2 = 50%
    }

    public function test_user_cannot_toggle_other_users_milestone(): void
    {
        $otherUser = User::factory()->create();
        $goal = Goal::create([
            'user_id' => $otherUser->id,
            'title' => 'Private',
            'status' => 'active',
            'progress' => 0,
            'milestones' => [['id' => 1, 'title' => 'Step', 'completed' => false]],
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/goals/{$goal->id}/milestones/1/toggle");

        $response->assertStatus(403);
    }
}
