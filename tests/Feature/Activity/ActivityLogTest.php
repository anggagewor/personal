<?php

namespace Tests\Feature\Activity;

use Modules\Activity\Infrastructure\Models\ActivityLogModel as ActivityLog;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_activity_logs(): void
    {
        ActivityLog::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/activities');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_user_cannot_see_other_users_logs(): void
    {
        $otherUser = User::factory()->create();
        ActivityLog::factory()->count(3)->create(['user_id' => $otherUser->id]);
        ActivityLog::factory()->count(2)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/activities');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_activity_logs_are_paginated(): void
    {
        ActivityLog::factory()->count(25)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/activities?per_page=10');

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 25);
    }

    public function test_activity_logs_ordered_by_latest(): void
    {
        $old = ActivityLog::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDay(),
        ]);
        $new = ActivityLog::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/activities');

        $data = $response->json('data');
        $this->assertEquals($new->id, $data[0]['id']);
    }

    public function test_unauthenticated_user_cannot_access_logs(): void
    {
        $response = $this->getJson('/api/activities');
        $response->assertStatus(401);
    }
}
