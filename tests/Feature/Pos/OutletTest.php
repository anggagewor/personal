<?php

namespace Tests\Feature\Pos;

use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutletTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_create_outlet(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/pos/outlets', [
            'name' => 'Warung Makan Sederhana',
            'business_type' => 'warung',
            'address' => 'Jl. Merdeka No. 1',
            'phone' => '08123456789',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Warung Makan Sederhana')
            ->assertJsonPath('data.business_type', 'warung');

        $this->assertDatabaseHas('pos_outlets', [
            'name' => 'Warung Makan Sederhana',
            'user_id' => $this->user->id,
            'business_type' => 'warung',
        ]);
    }

    public function test_create_outlet_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/pos/outlets', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'business_type']);
    }

    public function test_create_outlet_validates_business_type(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/pos/outlets', [
            'name' => 'My Shop',
            'business_type' => 'invalid_type',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['business_type']);
    }

    public function test_user_can_list_own_outlets(): void
    {
        OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Outlet A',
            'business_type' => 'retail',
        ]);
        OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Outlet B',
            'business_type' => 'kafe',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/pos/outlets');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_cannot_see_other_users_outlets(): void
    {
        $otherUser = User::factory()->create();
        OutletModel::create([
            'user_id' => $otherUser->id,
            'name' => 'Other Outlet',
            'business_type' => 'retail',
        ]);
        OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'My Outlet',
            'business_type' => 'retail',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/pos/outlets');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_update_outlet(): void
    {
        $outlet = OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Old Name',
            'business_type' => 'retail',
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/pos/outlets/{$outlet->id}", [
            'name' => 'New Name',
            'business_type' => 'kafe',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.business_type', 'kafe');

        $this->assertDatabaseHas('pos_outlets', [
            'id' => $outlet->id,
            'name' => 'New Name',
            'business_type' => 'kafe',
        ]);
    }

    public function test_user_cannot_update_other_users_outlet(): void
    {
        $otherUser = User::factory()->create();
        $outlet = OutletModel::create([
            'user_id' => $otherUser->id,
            'name' => 'Not Mine',
            'business_type' => 'retail',
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/pos/outlets/{$outlet->id}", [
            'name' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_soft_delete_outlet(): void
    {
        $outlet = OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'To Delete',
            'business_type' => 'retail',
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/pos/outlets/{$outlet->id}");

        $response->assertOk();
        $this->assertSoftDeleted('pos_outlets', ['id' => $outlet->id]);
    }

    public function test_user_cannot_delete_other_users_outlet(): void
    {
        $otherUser = User::factory()->create();
        $outlet = OutletModel::create([
            'user_id' => $otherUser->id,
            'name' => 'Not Mine',
            'business_type' => 'retail',
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/pos/outlets/{$outlet->id}");

        $response->assertStatus(403);
    }

    public function test_update_nonexistent_outlet_returns_404(): void
    {
        $response = $this->actingAs($this->user)->putJson('/api/pos/outlets/9999', [
            'name' => 'Does Not Exist',
        ]);

        $response->assertStatus(404);
    }

    public function test_delete_nonexistent_outlet_returns_404(): void
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/pos/outlets/9999');

        $response->assertStatus(404);
    }
}
