<?php

namespace Tests\Feature\Pos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Pos\Infrastructure\Models\TableModel;
use Modules\Pos\Infrastructure\Models\TableSessionModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Tests\TestCase;

class TableOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private OutletModel $kafeOutlet;

    private OutletModel $retailOutlet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $this->kafeOutlet = OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Kafe Luwak',
            'business_type' => 'kafe',
            'payment_flow' => 'both',
        ]);

        $this->retailOutlet = OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Toko Retail',
            'business_type' => 'retail',
            'payment_flow' => 'pay_first',
        ]);
    }

    public function test_can_create_table_for_kafe_outlet(): void
    {
        $response = $this->actingAs($this->user)->postJson(
            "/api/pos/outlets/{$this->kafeOutlet->id}/tables",
            ['name' => 'Meja 1']
        );

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Meja 1');

        $this->assertDatabaseHas('pos_tables', [
            'outlet_id' => $this->kafeOutlet->id,
            'name' => 'Meja 1',
        ]);
    }

    public function test_reject_table_creation_for_retail_outlet(): void
    {
        $response = $this->actingAs($this->user)->postJson(
            "/api/pos/outlets/{$this->retailOutlet->id}/tables",
            ['name' => 'Meja 1']
        );

        $response->assertStatus(500);
    }

    public function test_can_list_tables_for_outlet(): void
    {
        TableModel::create([
            'outlet_id' => $this->kafeOutlet->id,
            'name' => 'Meja A',
            'token' => 'token-a-123456',
            'is_active' => true,
        ]);
        TableModel::create([
            'outlet_id' => $this->kafeOutlet->id,
            'name' => 'Meja B',
            'token' => 'token-b-654321',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->getJson(
            "/api/pos/outlets/{$this->kafeOutlet->id}/tables"
        );

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_delete_table(): void
    {
        $table = TableModel::create([
            'outlet_id' => $this->kafeOutlet->id,
            'name' => 'Meja Hapus',
            'token' => 'token-delete-123',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/pos/tables/{$table->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('pos_tables', ['id' => $table->id]);
    }

    public function test_can_close_table_session(): void
    {
        $table = TableModel::create([
            'outlet_id' => $this->kafeOutlet->id,
            'name' => 'Meja Sesi',
            'token' => 'token-session-123',
            'is_active' => true,
        ]);

        TableSessionModel::create([
            'table_id' => $table->id,
            'status' => 'active',
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/pos/tables/{$table->id}/close-session");

        $response->assertOk();

        $this->assertDatabaseHas('pos_table_sessions', [
            'table_id' => $table->id,
            'status' => 'closed',
        ]);
    }

    public function test_close_session_fails_when_no_active_session(): void
    {
        $table = TableModel::create([
            'outlet_id' => $this->kafeOutlet->id,
            'name' => 'Meja Kosong',
            'token' => 'token-empty-123',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/pos/tables/{$table->id}/close-session");

        $response->assertStatus(500);
    }
}
