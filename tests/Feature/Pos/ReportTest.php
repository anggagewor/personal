<?php

namespace Tests\Feature\Pos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Pos\Infrastructure\Models\TransactionItemModel;
use Modules\Pos\Infrastructure\Models\TransactionModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Tests\TestCase;

class ReportTest extends TestCase
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
            'name' => 'Outlet Report',
            'business_type' => 'kafe',
            'payment_flow' => 'pay_first',
        ]);
    }

    public function test_daily_summary_returns_correct_totals(): void
    {
        $today = now()->toDateString();

        $this->createTransaction(50000, 'completed', 'cash');
        $this->createTransaction(30000, 'completed', 'e_wallet');

        $response = $this->actingAs($this->user)->getJson(
            "/api/pos/outlets/{$this->outlet->id}/reports/daily?date={$today}"
        );

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals(80000, $data['revenue']);
        $this->assertEquals(2, $data['count']);
        $this->assertEquals(40000, $data['average']);
    }

    public function test_daily_summary_excludes_voided_transactions(): void
    {
        $today = now()->toDateString();

        $this->createTransaction(50000, 'completed', 'cash');
        $this->createTransaction(30000, 'voided', 'cash');

        $response = $this->actingAs($this->user)->getJson(
            "/api/pos/outlets/{$this->outlet->id}/reports/daily?date={$today}"
        );

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals(50000, $data['revenue']);
        $this->assertEquals(1, $data['count']);
    }

    public function test_returns_zero_values_for_dates_with_no_transactions(): void
    {
        $emptyDate = '2020-01-01';

        $response = $this->actingAs($this->user)->getJson(
            "/api/pos/outlets/{$this->outlet->id}/reports/daily?date={$emptyDate}"
        );

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals(0, $data['revenue']);
        $this->assertEquals(0, $data['count']);
        $this->assertEquals(0, $data['average']);
    }

    public function test_date_range_report(): void
    {
        $from = now()->subDays(2)->toDateString();
        $to = now()->toDateString();

        $this->createTransaction(25000, 'completed', 'cash');

        $response = $this->actingAs($this->user)->getJson(
            "/api/pos/outlets/{$this->outlet->id}/reports/range?from={$from}&to={$to}"
        );

        $response->assertOk();

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertCount(3, $data); // 3 days inclusive
    }

    public function test_product_ranking_ordered_by_quantity_desc(): void
    {
        $today = now()->toDateString();

        // Product A sold 5 units
        $txn1 = $this->createTransaction(100000, 'completed', 'cash');
        TransactionItemModel::create([
            'transaction_id' => $txn1->id,
            'product_id' => 1,
            'product_name' => 'Product A',
            'quantity' => 5,
            'unit_price' => 20000,
            'subtotal' => 100000,
        ]);

        // Product B sold 10 units
        $txn2 = $this->createTransaction(50000, 'completed', 'cash');
        TransactionItemModel::create([
            'transaction_id' => $txn2->id,
            'product_id' => 2,
            'product_name' => 'Product B',
            'quantity' => 10,
            'unit_price' => 5000,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($this->user)->getJson(
            "/api/pos/outlets/{$this->outlet->id}/reports/products?from={$today}&to={$today}"
        );

        $response->assertOk();

        $data = $response->json('data');
        $this->assertCount(2, $data);
        // Product B (10 units) should come first
        $this->assertEquals('Product B', $data[0]['product_name']);
        $this->assertEquals(10, $data[0]['total_quantity']);
        $this->assertEquals('Product A', $data[1]['product_name']);
        $this->assertEquals(5, $data[1]['total_quantity']);
    }

    public function test_revenue_by_payment_method_groups_correctly(): void
    {
        $today = now()->toDateString();

        $this->createTransaction(40000, 'completed', 'cash');
        $this->createTransaction(60000, 'completed', 'cash');
        $this->createTransaction(25000, 'completed', 'e_wallet');

        $response = $this->actingAs($this->user)->getJson(
            "/api/pos/outlets/{$this->outlet->id}/reports/payments?from={$today}&to={$today}"
        );

        $response->assertOk();

        $data = $response->json('data');
        $this->assertIsArray($data);

        $cashGroup = collect($data)->firstWhere('payment_method_type', 'cash');
        $ewalletGroup = collect($data)->firstWhere('payment_method_type', 'e_wallet');

        $this->assertNotNull($cashGroup);
        $this->assertEquals(100000, $cashGroup['total_revenue']);
        $this->assertEquals(2, $cashGroup['count']);

        $this->assertNotNull($ewalletGroup);
        $this->assertEquals(25000, $ewalletGroup['total_revenue']);
        $this->assertEquals(1, $ewalletGroup['count']);
    }

    private function createTransaction(float $total, string $status, string $paymentMethodType): TransactionModel
    {
        static $seq = 0;
        $seq++;

        return TransactionModel::create([
            'outlet_id' => $this->outlet->id,
            'transaction_number' => 'TRX-' . now()->format('ymd') . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT),
            'subtotal' => $total,
            'discount_amount' => 0,
            'total' => $total,
            'payment_method' => $paymentMethodType === 'cash' ? 'Tunai' : 'E-Wallet',
            'payment_method_type' => $paymentMethodType,
            'status' => $status,
        ]);
    }
}
