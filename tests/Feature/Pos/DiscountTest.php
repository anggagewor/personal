<?php

namespace Tests\Feature\Pos;

use Modules\Pos\Infrastructure\Models\DiscountModel;
use Modules\Pos\Infrastructure\Models\MemberModel;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private OutletModel $outlet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->outlet = OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Warung Test',
            'business_type' => 'warung',
            'payment_flow' => 'pay_first',
        ]);
    }

    public function test_user_can_list_discounts(): void
    {
        DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Diskon 10%',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'priority' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/pos/outlets/{$this->outlet->id}/discounts");

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_create_discount(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/pos/outlets/{$this->outlet->id}/discounts", [
                'name' => 'Diskon Akhir Tahun',
                'type' => 'percentage',
                'value' => 15,
                'min_purchase' => 50000,
                'member_only' => false,
                'is_active' => true,
                'priority' => 1,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Diskon Akhir Tahun')
            ->assertJsonPath('data.type', 'percentage');
        $this->assertEquals(15, $response->json('data.value'));

        $this->assertDatabaseHas('pos_discounts', [
            'outlet_id' => $this->outlet->id,
            'name' => 'Diskon Akhir Tahun',
            'type' => 'percentage',
        ]);
    }

    public function test_user_can_update_discount(): void
    {
        $discount = DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Diskon Lama',
            'type' => 'fixed',
            'value' => 5000,
            'is_active' => true,
            'priority' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/pos/discounts/{$discount->id}", [
                'name' => 'Diskon Baru',
                'type' => 'fixed',
                'value' => 10000,
                'is_active' => true,
                'priority' => 1,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Diskon Baru');
        $this->assertEquals(10000, $response->json('data.value'));
    }

    public function test_user_can_delete_discount(): void
    {
        $discount = DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Akan Dihapus',
            'type' => 'percentage',
            'value' => 5,
            'is_active' => true,
            'priority' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/pos/discounts/{$discount->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('pos_discounts', ['id' => $discount->id]);
    }

    public function test_evaluate_returns_applicable_discounts(): void
    {
        DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Diskon 10% min 50rb',
            'type' => 'percentage',
            'value' => 10,
            'min_purchase' => 50000,
            'is_active' => true,
            'priority' => 0,
        ]);

        DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Diskon 5rb min 100rb',
            'type' => 'fixed',
            'value' => 5000,
            'min_purchase' => 100000,
            'is_active' => true,
            'priority' => 1,
        ]);

        // Subtotal 75000 — only first discount applies
        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/discounts/evaluate', [
                'outlet_id' => $this->outlet->id,
                'subtotal' => 75000,
            ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data');
        $this->assertEquals('Diskon 10% min 50rb', $response->json('data.0.name'));
    }

    public function test_evaluate_excludes_inactive_discounts(): void
    {
        DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Diskon Aktif',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'priority' => 0,
        ]);

        DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Diskon Nonaktif',
            'type' => 'percentage',
            'value' => 20,
            'is_active' => false,
            'priority' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/discounts/evaluate', [
                'outlet_id' => $this->outlet->id,
                'subtotal' => 100000,
            ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data');
        $this->assertEquals('Diskon Aktif', $response->json('data.0.name'));
    }

    public function test_evaluate_excludes_member_only_discount_for_walk_in(): void
    {
        DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Diskon Member',
            'type' => 'percentage',
            'value' => 15,
            'member_only' => true,
            'is_active' => true,
            'priority' => 0,
        ]);

        DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Diskon Umum',
            'type' => 'fixed',
            'value' => 2000,
            'member_only' => false,
            'is_active' => true,
            'priority' => 1,
        ]);

        // No member_id → walk-in → member_only excluded
        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/discounts/evaluate', [
                'outlet_id' => $this->outlet->id,
                'subtotal' => 100000,
                'member_id' => null,
            ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data');
        $this->assertEquals('Diskon Umum', $response->json('data.0.name'));
    }

    public function test_evaluate_includes_member_only_discount_when_member_provided(): void
    {
        $member = MemberModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Budi',
            'phone' => '08123456789',
        ]);

        DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Diskon Member',
            'type' => 'percentage',
            'value' => 15,
            'member_only' => true,
            'is_active' => true,
            'priority' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/discounts/evaluate', [
                'outlet_id' => $this->outlet->id,
                'subtotal' => 100000,
                'member_id' => $member->id,
            ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data');
        $this->assertEquals('Diskon Member', $response->json('data.0.name'));
    }

    public function test_create_discount_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/pos/outlets/{$this->outlet->id}/discounts", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type', 'value']);
    }

    public function test_unauthenticated_user_cannot_access_discounts(): void
    {
        $response = $this->getJson("/api/pos/outlets/{$this->outlet->id}/discounts");

        $response->assertStatus(401);
    }

    public function test_user_cannot_access_other_users_discounts(): void
    {
        $otherUser = User::factory()->create();
        $otherOutlet = OutletModel::create([
            'user_id' => $otherUser->id,
            'name' => 'Outlet Lain',
            'business_type' => 'kafe',
            'payment_flow' => 'pay_first',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/pos/outlets/{$otherOutlet->id}/discounts");

        $response->assertStatus(403);
    }
}
