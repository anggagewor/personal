<?php

namespace Tests\Feature\Finance;

use Modules\Finance\Infrastructure\Models\FinanceModel as Finance;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_finances(): void
    {
        Finance::create(['user_id' => $this->user->id, 'type' => 'income', 'category' => 'salary', 'amount' => 5000, 'date' => now()]);
        Finance::create(['user_id' => $this->user->id, 'type' => 'expense', 'category' => 'food', 'amount' => 50, 'date' => now()]);

        $response = $this->actingAs($this->user)->getJson('/api/finances');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_user_can_filter_by_month(): void
    {
        Finance::create(['user_id' => $this->user->id, 'type' => 'income', 'category' => 'salary', 'amount' => 5000, 'date' => '2026-07-15']);
        Finance::create(['user_id' => $this->user->id, 'type' => 'expense', 'category' => 'food', 'amount' => 50, 'date' => '2026-06-10']);

        $response = $this->actingAs($this->user)->getJson('/api/finances?month=2026-07');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_filter_by_type(): void
    {
        Finance::create(['user_id' => $this->user->id, 'type' => 'income', 'category' => 'salary', 'amount' => 5000, 'date' => now()]);
        Finance::create(['user_id' => $this->user->id, 'type' => 'expense', 'category' => 'food', 'amount' => 50, 'date' => now()]);

        $response = $this->actingAs($this->user)->getJson('/api/finances?type=income');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_create_finance(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/finances', [
            'type' => 'expense',
            'category' => 'transport',
            'amount' => 25.50,
            'description' => 'Grab',
            'date' => '2026-07-24',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.category', 'transport');
    }

    public function test_create_finance_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/finances', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type', 'category', 'amount', 'date']);
    }

    public function test_create_finance_validates_type(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/finances', [
            'type' => 'invalid',
            'category' => 'test',
            'amount' => 100,
            'date' => '2026-07-24',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_user_can_update_finance(): void
    {
        $finance = Finance::create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'category' => 'food',
            'amount' => 50,
            'date' => now(),
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/finances/{$finance->id}", [
            'amount' => 75,
        ]);

        $response->assertOk();
        $this->assertEquals(75, $finance->fresh()->amount);
    }

    public function test_user_cannot_update_other_users_finance(): void
    {
        $otherUser = User::factory()->create();
        $finance = Finance::create(['user_id' => $otherUser->id, 'type' => 'income', 'category' => 'salary', 'amount' => 5000, 'date' => now()]);

        $response = $this->actingAs($this->user)->putJson("/api/finances/{$finance->id}", [
            'amount' => 0,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_finance(): void
    {
        $finance = Finance::create(['user_id' => $this->user->id, 'type' => 'expense', 'category' => 'food', 'amount' => 50, 'date' => now()]);

        $response = $this->actingAs($this->user)->deleteJson("/api/finances/{$finance->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('finances', ['id' => $finance->id]);
    }

    public function test_user_can_get_summary(): void
    {
        $month = now()->format('Y-m');
        Finance::create(['user_id' => $this->user->id, 'type' => 'income', 'category' => 'salary', 'amount' => 5000, 'date' => now()]);
        Finance::create(['user_id' => $this->user->id, 'type' => 'expense', 'category' => 'food', 'amount' => 200, 'date' => now()]);
        Finance::create(['user_id' => $this->user->id, 'type' => 'expense', 'category' => 'transport', 'amount' => 100, 'date' => now()]);

        $response = $this->actingAs($this->user)->getJson("/api/finances/summary?month={$month}");

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals(5000, $data['income']);
        $this->assertEquals(300, $data['expense']);
        $this->assertEquals(4700, $data['balance']);
    }
}
