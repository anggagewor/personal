<?php

namespace Tests\Feature\Pos;

use Modules\Pos\Infrastructure\Models\DiscountModel;
use Modules\Pos\Infrastructure\Models\MemberModel;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberDiscountTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private OutletModel $outlet;
    private MemberModel $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->outlet = OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Kafe Test',
            'business_type' => 'kafe',
            'payment_flow' => 'pay_first',
        ]);
        $this->member = MemberModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Siti',
            'phone' => '08111222333',
        ]);
    }

    public function test_member_only_discount_excluded_when_no_member(): void
    {
        DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Khusus Member',
            'type' => 'percentage',
            'value' => 20,
            'member_only' => true,
            'is_active' => true,
            'priority' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/discounts/evaluate', [
                'outlet_id' => $this->outlet->id,
                'subtotal' => 100000,
                'member_id' => null,
            ]);

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_member_only_discount_included_when_member_provided(): void
    {
        DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Khusus Member',
            'type' => 'percentage',
            'value' => 20,
            'member_only' => true,
            'is_active' => true,
            'priority' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/discounts/evaluate', [
                'outlet_id' => $this->outlet->id,
                'subtotal' => 100000,
                'member_id' => $this->member->id,
            ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data');
        $this->assertEquals('Khusus Member', $response->json('data.0.name'));
    }

    public function test_non_member_only_discount_applies_to_walk_in(): void
    {
        DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Diskon Semua',
            'type' => 'fixed',
            'value' => 5000,
            'member_only' => false,
            'is_active' => true,
            'priority' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/discounts/evaluate', [
                'outlet_id' => $this->outlet->id,
                'subtotal' => 100000,
                'member_id' => null,
            ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data');
        $this->assertEquals('Diskon Semua', $response->json('data.0.name'));
    }

    public function test_priority_ordering_applies_discounts_in_order(): void
    {
        DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Priority 2',
            'type' => 'percentage',
            'value' => 5,
            'member_only' => false,
            'is_active' => true,
            'priority' => 2,
        ]);

        DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Priority 0',
            'type' => 'fixed',
            'value' => 3000,
            'member_only' => false,
            'is_active' => true,
            'priority' => 0,
        ]);

        DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Priority 1',
            'type' => 'percentage',
            'value' => 10,
            'member_only' => false,
            'is_active' => true,
            'priority' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/discounts/evaluate', [
                'outlet_id' => $this->outlet->id,
                'subtotal' => 100000,
            ]);

        $response->assertOk();
        $data = $response->json('data');
        // Priority ordering: 0, 1, 2
        $this->assertEquals('Priority 0', $data[0]['name']);
        $this->assertEquals('Priority 1', $data[1]['name']);
        $this->assertEquals('Priority 2', $data[2]['name']);
    }

    public function test_member_only_checked_after_other_conditions(): void
    {
        // Member-only discount that fails min_purchase — should be excluded regardless of member
        DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Member + Min 200rb',
            'type' => 'percentage',
            'value' => 25,
            'min_purchase' => 200000,
            'member_only' => true,
            'is_active' => true,
            'priority' => 0,
        ]);

        // With member but subtotal below min_purchase
        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/discounts/evaluate', [
                'outlet_id' => $this->outlet->id,
                'subtotal' => 100000,
                'member_id' => $this->member->id,
            ]);

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_inactive_member_only_discount_excluded_even_with_member(): void
    {
        DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Nonaktif Member',
            'type' => 'percentage',
            'value' => 30,
            'member_only' => true,
            'is_active' => false,
            'priority' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/discounts/evaluate', [
                'outlet_id' => $this->outlet->id,
                'subtotal' => 100000,
                'member_id' => $this->member->id,
            ]);

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_date_range_expired_discount_excluded_even_with_member(): void
    {
        DiscountModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Expired Member',
            'type' => 'percentage',
            'value' => 20,
            'member_only' => true,
            'is_active' => true,
            'priority' => 0,
            'starts_at' => now()->subDays(30),
            'ends_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/discounts/evaluate', [
                'outlet_id' => $this->outlet->id,
                'subtotal' => 100000,
                'member_id' => $this->member->id,
            ]);

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
