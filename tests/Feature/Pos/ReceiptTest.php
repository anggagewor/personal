<?php

namespace Tests\Feature\Pos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Pos\Infrastructure\Models\TransactionItemModel;
use Modules\Pos\Infrastructure\Models\TransactionModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Tests\TestCase;

class ReceiptTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private OutletModel $outlet;

    private TransactionModel $transaction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $this->outlet = OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Kafe Receipt',
            'business_type' => 'kafe',
            'payment_flow' => 'pay_first',
            'address' => 'Jl. Kenangan No. 7',
        ]);

        $this->transaction = TransactionModel::create([
            'outlet_id' => $this->outlet->id,
            'transaction_number' => 'TRX-260101-0001',
            'subtotal' => 50000,
            'discount_amount' => 5000,
            'total' => 45000,
            'payment_method' => 'Tunai',
            'payment_method_type' => 'cash',
            'amount_tendered' => 50000,
            'change_amount' => 5000,
            'status' => 'completed',
        ]);

        TransactionItemModel::create([
            'transaction_id' => $this->transaction->id,
            'product_id' => 1,
            'product_name' => 'Es Kopi',
            'variant_name' => 'Reguler',
            'quantity' => 2,
            'unit_price' => 20000,
            'subtotal' => 40000,
        ]);

        TransactionItemModel::create([
            'transaction_id' => $this->transaction->id,
            'product_id' => 2,
            'product_name' => 'Roti Bakar',
            'quantity' => 1,
            'unit_price' => 10000,
            'subtotal' => 10000,
        ]);
    }

    public function test_show_receipt_for_transaction(): void
    {
        $response = $this->actingAs($this->user)->getJson(
            "/api/pos/transactions/{$this->transaction->id}/receipt"
        );

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'outlet_name',
                    'outlet_address',
                    'transaction_number',
                    'date_time',
                    'items',
                    'discount_amount',
                    'subtotal',
                    'total',
                    'payment_method',
                ],
                'message',
            ]);
    }

    public function test_receipt_contains_all_required_fields(): void
    {
        $response = $this->actingAs($this->user)->getJson(
            "/api/pos/transactions/{$this->transaction->id}/receipt"
        );

        $response->assertOk();

        $data = $response->json('data');

        // Outlet info
        $this->assertEquals('Kafe Receipt', $data['outlet_name']);
        $this->assertEquals('Jl. Kenangan No. 7', $data['outlet_address']);

        // Transaction number & datetime
        $this->assertEquals('TRX-260101-0001', $data['transaction_number']);
        $this->assertNotEmpty($data['date_time']);

        // Items
        $this->assertCount(2, $data['items']);
        $this->assertEquals('Es Kopi', $data['items'][0]['name']);
        $this->assertEquals(2, $data['items'][0]['quantity']);
        $this->assertEquals(20000, $data['items'][0]['unit_price']);
        $this->assertEquals(40000, $data['items'][0]['subtotal']);

        // Totals
        $this->assertEquals(50000, $data['subtotal']);
        $this->assertEquals(5000, $data['discount_amount']);
        $this->assertEquals(45000, $data['total']);

        // Payment
        $this->assertEquals('Tunai', $data['payment_method']);
        $this->assertEquals(50000, $data['amount_tendered']);
        $this->assertEquals(5000, $data['change']);
    }

    public function test_reprint_returns_same_data(): void
    {
        $first = $this->actingAs($this->user)->getJson(
            "/api/pos/transactions/{$this->transaction->id}/receipt"
        );

        $second = $this->actingAs($this->user)->getJson(
            "/api/pos/transactions/{$this->transaction->id}/receipt"
        );

        $first->assertOk();
        $second->assertOk();

        $this->assertEquals($first->json('data'), $second->json('data'));
    }

    public function test_receipt_for_nonexistent_transaction_returns_404(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/pos/transactions/99999/receipt');

        $response->assertStatus(404);
    }
}
