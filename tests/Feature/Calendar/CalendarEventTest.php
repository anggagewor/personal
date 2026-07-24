<?php

namespace Tests\Feature\Calendar;

use Modules\Calendar\Infrastructure\Models\CalendarEventModel as CalendarEvent;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarEventTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_events(): void
    {
        CalendarEvent::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/calendar-events?start_date=' . now()->subMonth()->toDateString() . '&end_date=' . now()->addMonths(2)->toDateString());

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_filter_events_by_date_range(): void
    {
        CalendarEvent::factory()->create([
            'user_id' => $this->user->id,
            'start_at' => '2026-07-01 10:00:00',
        ]);
        CalendarEvent::factory()->create([
            'user_id' => $this->user->id,
            'start_at' => '2026-08-15 10:00:00',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/calendar-events?start_date=2026-07-01&end_date=2026-07-31');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_create_event(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/calendar-events', [
            'title' => 'Meeting',
            'start_at' => '2026-08-01 09:00:00',
            'end_at' => '2026-08-01 10:00:00',
            'all_day' => false,
            'color' => '#FF5733',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Meeting');

        $this->assertDatabaseHas('calendar_events', ['title' => 'Meeting']);
    }

    public function test_user_can_create_all_day_event(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/calendar-events', [
            'title' => 'Holiday',
            'start_at' => '2026-08-17 00:00:00',
            'all_day' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.isAllDay', true);
    }

    public function test_user_can_update_event(): void
    {
        $event = CalendarEvent::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->putJson("/api/calendar-events/{$event->id}", [
            'title' => 'Updated Event',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated Event');
    }

    public function test_user_cannot_update_other_users_event(): void
    {
        $otherUser = User::factory()->create();
        $event = CalendarEvent::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->putJson("/api/calendar-events/{$event->id}", [
            'title' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_event(): void
    {
        $event = CalendarEvent::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/calendar-events/{$event->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('calendar_events', ['id' => $event->id]);
    }

    public function test_user_cannot_delete_other_users_event(): void
    {
        $otherUser = User::factory()->create();
        $event = CalendarEvent::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/calendar-events/{$event->id}");

        $response->assertStatus(403);
    }
}
