<?php

namespace Tests\Feature\Quote;

use Modules\Quote\Infrastructure\Models\QuoteModel as Quote;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_today_quote(): void
    {
        $user = User::factory()->create();
        Quote::create(['content' => 'Stay hungry, stay foolish.', 'author' => 'Steve Jobs']);
        Quote::create(['content' => 'Done is better than perfect.', 'author' => 'Sheryl Sandberg']);

        $response = $this->actingAs($user)->getJson('/api/quotes/today');

        $response->assertOk()
            ->assertJsonStructure(['data' => ['content', 'author']]);
    }

    public function test_today_returns_null_when_no_quotes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/quotes/today');

        $response->assertOk()
            ->assertJsonPath('data', null);
    }

    public function test_quote_rotates_by_day(): void
    {
        $user = User::factory()->create();
        Quote::create(['content' => 'Quote 1', 'author' => 'Author 1']);
        Quote::create(['content' => 'Quote 2', 'author' => 'Author 2']);
        Quote::create(['content' => 'Quote 3', 'author' => 'Author 3']);

        $response = $this->actingAs($user)->getJson('/api/quotes/today');

        $response->assertOk();
        $this->assertNotNull($response->json('data.content'));
    }

    public function test_unauthenticated_user_cannot_access_quotes(): void
    {
        $response = $this->getJson('/api/quotes/today');
        $response->assertStatus(401);
    }
}
