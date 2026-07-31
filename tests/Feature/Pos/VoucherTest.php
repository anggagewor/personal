<?php

namespace Tests\Feature\Pos;

use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Pos\Infrastructure\Models\VoucherModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherTest extends TestCase
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
            'name' => 'Toko Test',
            'business_type' => 'retail',
            'payment_flow' => 'pay_first',
        ]);
    }

    public function test_user_can_list_vouchers(): void
    {
        VoucherModel::create([
            'outlet_id' => $this->outlet->id,
            'code' => 'PROMO10',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/pos/outlets/{$this->outlet->id}/vouchers");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_user_can_create_voucher(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/pos/outlets/{$this->outlet->id}/vouchers", [
                'code' => 'DISKON50K',
                'discount_type' => 'fixed',
                'discount_value' => 50000,
                'min_purchase' => 200000,
                'usage_limit' => 100,
                'expires_at' => now()->addMonth()->toDateTimeString(),
                'is_active' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.code', 'DISKON50K')
            ->assertJsonPath('data.discount_type', 'fixed');
        $this->assertEquals(50000, $response->json('data.discount_value'));

        $this->assertDatabaseHas('pos_vouchers', [
            'outlet_id' => $this->outlet->id,
            'code' => 'DISKON50K',
        ]);
    }

    public function test_user_can_batch_create_vouchers_with_prefix(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/pos/outlets/{$this->outlet->id}/vouchers/batch", [
                'prefix' => 'PROMO',
                'count' => 5,
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'usage_limit' => 1,
                'is_active' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonCount(5, 'data');

        // All codes should start with prefix
        $codes = collect($response->json('data'))->pluck('code');
        foreach ($codes as $code) {
            $this->assertStringStartsWith('PROMO', $code);
        }

        // All codes should be unique
        $this->assertEquals($codes->count(), $codes->unique()->count());
    }

    public function test_validate_active_voucher(): void
    {
        VoucherModel::create([
            'outlet_id' => $this->outlet->id,
            'code' => 'VALID10',
            'type' => 'percentage',
            'value' => 10,
            'min_purchase' => 50000,
            'usage_limit' => 10,
            'usage_count' => 0,
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/vouchers/validate', [
                'code' => 'VALID10',
                'subtotal' => 100000,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.code', 'VALID10');
    }

    public function test_validate_expired_voucher_rejected(): void
    {
        VoucherModel::create([
            'outlet_id' => $this->outlet->id,
            'code' => 'EXPIRED',
            'type' => 'percentage',
            'value' => 10,
            'expires_at' => now()->subDay(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/vouchers/validate', [
                'code' => 'EXPIRED',
                'subtotal' => 100000,
            ]);

        $response->assertStatus(422);
    }

    public function test_validate_usage_limit_reached_rejected(): void
    {
        VoucherModel::create([
            'outlet_id' => $this->outlet->id,
            'code' => 'MAXED',
            'type' => 'fixed',
            'value' => 5000,
            'usage_limit' => 3,
            'usage_count' => 3,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/vouchers/validate', [
                'code' => 'MAXED',
                'subtotal' => 100000,
            ]);

        $response->assertStatus(422);
    }

    public function test_validate_below_min_purchase_rejected(): void
    {
        VoucherModel::create([
            'outlet_id' => $this->outlet->id,
            'code' => 'MINPURCH',
            'type' => 'percentage',
            'value' => 15,
            'min_purchase' => 100000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/vouchers/validate', [
                'code' => 'MINPURCH',
                'subtotal' => 50000,
            ]);

        $response->assertStatus(422);
    }

    public function test_validate_nonexistent_code_returns_422(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/vouchers/validate', [
                'code' => 'NOTEXIST',
                'subtotal' => 100000,
            ]);

        $response->assertStatus(422);
    }

    public function test_create_voucher_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/pos/outlets/{$this->outlet->id}/vouchers", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'discount_type', 'discount_value']);
    }

    public function test_create_voucher_rejects_duplicate_code(): void
    {
        VoucherModel::create([
            'outlet_id' => $this->outlet->id,
            'code' => 'UNIQUE1',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/pos/outlets/{$this->outlet->id}/vouchers", [
                'code' => 'UNIQUE1',
                'discount_type' => 'percentage',
                'discount_value' => 5,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_unauthenticated_user_cannot_access_vouchers(): void
    {
        $response = $this->getJson("/api/pos/outlets/{$this->outlet->id}/vouchers");

        $response->assertStatus(401);
    }

    public function test_user_cannot_access_other_users_vouchers(): void
    {
        $otherUser = User::factory()->create();
        $otherOutlet = OutletModel::create([
            'user_id' => $otherUser->id,
            'name' => 'Outlet Lain',
            'business_type' => 'retail',
            'payment_flow' => 'pay_first',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/pos/outlets/{$otherOutlet->id}/vouchers");

        $response->assertStatus(403);
    }

    public function test_user_can_view_voucher_detail(): void
    {
        $voucher = VoucherModel::create([
            'outlet_id' => $this->outlet->id,
            'code' => 'DETAIL1',
            'type' => 'fixed',
            'value' => 25000,
            'usage_limit' => 50,
            'usage_count' => 12,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/pos/vouchers/{$voucher->id}");

        $response->assertOk()
            ->assertJsonPath('data.code', 'DETAIL1')
            ->assertJsonPath('data.usage_count', 12)
            ->assertJsonPath('data.usage_limit', 50);
    }
}
