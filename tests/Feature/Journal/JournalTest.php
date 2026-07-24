<?php

namespace Tests\Feature\Journal;

use Modules\Journal\Infrastructure\Models\JournalModel as Journal;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_journals(): void
    {
        Journal::create(['user_id' => $this->user->id, 'date' => '2026-07-20', 'content' => 'Day 1']);
        Journal::create(['user_id' => $this->user->id, 'date' => '2026-07-21', 'content' => 'Day 2']);

        $response = $this->actingAs($this->user)->getJson('/api/journals');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_filter_by_month(): void
    {
        Journal::create(['user_id' => $this->user->id, 'date' => '2026-07-15', 'content' => 'July']);
        Journal::create(['user_id' => $this->user->id, 'date' => '2026-06-10', 'content' => 'June']);

        $response = $this->actingAs($this->user)->getJson('/api/journals?month=2026-07');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_create_journal(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/journals', [
            'date' => '2026-07-24',
            'content' => 'Hari ini produktif.',
            'mood' => 'happy',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.mood', 'happy');
    }

    public function test_create_journal_uses_upsert_for_same_date(): void
    {
        // First create - fresh entry
        $this->actingAs($this->user)->postJson('/api/journals', [
            'date' => '2026-07-20',
            'content' => 'Old',
            'mood' => 'neutral',
        ]);

        // Second create same date - should update
        $response = $this->actingAs($this->user)->postJson('/api/journals', [
            'date' => '2026-07-20',
            'content' => 'Updated',
            'mood' => 'excited',
        ]);

        $response->assertStatus(201);
        $journals = Journal::where('user_id', $this->user->id)->get();
        $this->assertCount(1, $journals);
        $this->assertEquals('Updated', $journals->first()->content);
    }

    public function test_create_journal_validates_mood(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/journals', [
            'date' => '2026-07-24',
            'content' => 'Test',
            'mood' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['mood']);
    }

    public function test_user_can_show_journal_by_date(): void
    {
        $this->actingAs($this->user)->postJson('/api/journals', [
            'date' => '2026-07-20',
            'content' => 'Today',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/journals/2026-07-20');

        $response->assertOk()
            ->assertJsonPath('data.content', 'Today');
    }

    public function test_show_returns_null_for_missing_date(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/journals/2026-01-01');

        $response->assertOk()
            ->assertJsonPath('data', null);
    }

    public function test_user_can_delete_journal(): void
    {
        $journal = Journal::create(['user_id' => $this->user->id, 'date' => '2026-07-24', 'content' => 'Delete me']);

        $response = $this->actingAs($this->user)->deleteJson("/api/journals/{$journal->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('journals', ['id' => $journal->id]);
    }

    public function test_user_cannot_delete_other_users_journal(): void
    {
        $otherUser = User::factory()->create();
        $journal = Journal::create(['user_id' => $otherUser->id, 'date' => '2026-07-24', 'content' => 'Private']);

        $response = $this->actingAs($this->user)->deleteJson("/api/journals/{$journal->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_get_moods(): void
    {
        Journal::create(['user_id' => $this->user->id, 'date' => '2026-07-20', 'content' => 'A', 'mood' => 'happy']);
        Journal::create(['user_id' => $this->user->id, 'date' => '2026-07-21', 'content' => 'B', 'mood' => 'sad']);
        Journal::create(['user_id' => $this->user->id, 'date' => '2026-07-22', 'content' => 'C', 'mood' => null]);

        $response = $this->actingAs($this->user)->getJson('/api/journals/moods');

        $response->assertOk()
            ->assertJsonCount(2, 'data'); // Only entries with mood
    }
}
