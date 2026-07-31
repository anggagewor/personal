<?php

namespace Tests\Feature\Pos;

use Modules\Pos\Infrastructure\Models\MemberModel;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Pos\Infrastructure\Models\TransactionModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberTest extends TestCase
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

    public function test_user_can_list_members(): void
    {
        MemberModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Budi',
            'phone' => '08111000111',
        ]);
        MemberModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Sari',
            'phone' => '08222000222',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/pos/outlets/{$this->outlet->id}/members");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_user_can_create_member(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/pos/outlets/{$this->outlet->id}/members", [
                'name' => 'Ahmad',
                'phone' => '08333000333',
                'email' => 'ahmad@example.com',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Ahmad')
            ->assertJsonPath('data.phone', '08333000333')
            ->assertJsonPath('data.email', 'ahmad@example.com');

        $this->assertDatabaseHas('pos_members', [
            'outlet_id' => $this->outlet->id,
            'name' => 'Ahmad',
            'phone' => '08333000333',
        ]);
    }

    public function test_user_can_update_member(): void
    {
        $member = MemberModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Lama',
            'phone' => '08444000444',
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/pos/members/{$member->id}", [
                'name' => 'Baru',
                'phone' => '08555000555',
                'email' => 'baru@example.com',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Baru')
            ->assertJsonPath('data.phone', '08555000555')
            ->assertJsonPath('data.email', 'baru@example.com');
    }

    public function test_user_can_delete_member(): void
    {
        $member = MemberModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Hapus',
            'phone' => '08666000666',
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/pos/members/{$member->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('pos_members', ['id' => $member->id]);
    }

    public function test_deletion_preserves_transaction_history(): void
    {
        $member = MemberModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Member Dihapus',
            'phone' => '08777000777',
        ]);

        // Create a transaction linked to this member
        $transaction = TransactionModel::create([
            'outlet_id' => $this->outlet->id,
            'transaction_number' => 'TRX-260731-001',
            'subtotal' => 50000,
            'discount_amount' => 0,
            'total' => 50000,
            'payment_method' => 'cash',
            'payment_method_type' => 'cash',
            'status' => 'completed',
            'member_id' => $member->id,
        ]);

        // Delete member
        $this->actingAs($this->user)
            ->deleteJson("/api/pos/members/{$member->id}");

        // Member deleted
        $this->assertDatabaseMissing('pos_members', ['id' => $member->id]);

        // Transaction data preserved
        $this->assertDatabaseHas('pos_transactions', [
            'id' => $transaction->id,
            'transaction_number' => 'TRX-260731-001',
            'total' => 50000,
            'member_id' => $member->id,
        ]);
    }

    public function test_search_members_by_name(): void
    {
        MemberModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Andi Prasetyo',
            'phone' => '08100000001',
        ]);
        MemberModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Budi Santoso',
            'phone' => '08100000002',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/pos/outlets/{$this->outlet->id}/members/search?q=Andi");

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Andi Prasetyo', $data[0]['name']);
    }

    public function test_search_members_by_phone(): void
    {
        MemberModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Citra',
            'phone' => '08199900001',
        ]);
        MemberModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Dewi',
            'phone' => '08288800002',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/pos/outlets/{$this->outlet->id}/members/search?q=08199");

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Citra', $data[0]['name']);
    }

    public function test_create_member_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/pos/outlets/{$this->outlet->id}/members", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'phone']);
    }

    public function test_unauthenticated_user_cannot_access_members(): void
    {
        $response = $this->getJson("/api/pos/outlets/{$this->outlet->id}/members");

        $response->assertStatus(401);
    }

    public function test_user_cannot_access_other_users_members(): void
    {
        $otherUser = User::factory()->create();
        $otherOutlet = OutletModel::create([
            'user_id' => $otherUser->id,
            'name' => 'Outlet Lain',
            'business_type' => 'warung',
            'payment_flow' => 'pay_first',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/pos/outlets/{$otherOutlet->id}/members");

        $response->assertStatus(403);
    }

    public function test_linking_fallback_on_invalid_member_id(): void
    {
        // When creating a transaction with an invalid member_id,
        // the system should fall back to walk-in (member_id = null).
        // This tests the controller/action-level fallback behavior.
        // We need a product to create a transaction.

        $categoryModel = \Modules\Pos\Infrastructure\Models\CategoryModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Makanan',
            'sort_order' => 0,
        ]);

        $productModel = \Modules\Pos\Infrastructure\Models\ProductModel::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $categoryModel->id,
            'name' => 'Nasi Goreng',
            'base_price' => 15000,
            'status' => 'active',
            'has_variants' => false,
            'track_stock' => false,
        ]);

        $variantModel = \Modules\Pos\Infrastructure\Models\ProductVariantModel::create([
            'product_id' => $productModel->id,
            'name' => 'default',
            'price' => 15000,
            'stock_quantity' => 0,
        ]);

        $paymentMethod = \Modules\Pos\Infrastructure\Models\PaymentMethodModel::create([
            'outlet_id' => $this->outlet->id,
            'type' => 'cash',
            'name' => 'Tunai',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/pos/outlets/{$this->outlet->id}/transactions", [
                'items' => [
                    [
                        'product_id' => $productModel->id,
                        'product_variant_id' => $variantModel->id,
                        'product_name' => 'Nasi Goreng',
                        'variant_name' => 'default',
                        'quantity' => 1,
                        'unit_price' => 15000,
                    ],
                ],
                'payment_method' => 'Tunai',
                'payment_method_type' => 'cash',
                'amount_tendered' => 20000,
                'member_id' => 99999, // Invalid member ID
            ]);

        // Should still succeed — fallback to walk-in
        $response->assertStatus(201);
        $this->assertNull($response->json('data.member_id'));
    }
}
