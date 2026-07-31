<?php

namespace Tests\Property\Supplier;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderItemModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderModel;
use Modules\Supplier\Infrastructure\Models\SupplierModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Property tests for Supplier data management (Properties 1-5).
 */
class SupplierPropertiesTest extends TestCase
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
            'name' => 'Test Outlet',
            'business_type' => 'retail',
        ]);
    }

    // =========================================================================
    // Property 1: Supplier data persistence round-trip
    // Validates: Requirements 1.1, 1.2
    // =========================================================================

    public static function supplierDataProvider(): array
    {
        return [
            'full data' => [[
                'name' => 'PT Sumber Makmur',
                'address' => 'Jl. Industri No. 5, Jakarta',
                'phone' => '08123456789',
                'email' => 'info@sumbermakmur.co.id',
                'bank_name' => 'BCA',
                'bank_account_number' => '1234567890',
                'bank_account_holder' => 'PT Sumber Makmur',
                'notes' => 'Supplier bahan pokok utama',
            ]],
            'minimal data (name only)' => [[
                'name' => 'CV Sentosa',
            ]],
            'with phone and email only' => [[
                'name' => 'Toko Jaya Abadi',
                'phone' => '02198765432',
                'email' => 'jaya@abadi.com',
            ]],
            'with bank details only' => [[
                'name' => 'UD Berkah Selalu',
                'bank_name' => 'Mandiri',
                'bank_account_number' => '0987654321',
                'bank_account_holder' => 'Budi Santoso',
            ]],
            'unicode characters' => [[
                'name' => 'Toko Résidence Élégant',
                'address' => 'Jl. Merdeka №10',
                'notes' => 'Supplier spesial — harga grosir',
            ]],
        ];
    }

    /**
     * Property 1: Round-trip data persistence.
     * For any supplier data, create → retrieve should return identical fields.
     *
     * **Validates: Requirements 1.1, 1.2**
     */
    #[DataProvider('supplierDataProvider')]
    public function test_property1_round_trip_persistence(array $data): void
    {
        $response = $this->actingAs($this->user)->postJson(
            "/api/supplier/outlets/{$this->outlet->id}/suppliers",
            $data
        );

        $response->assertStatus(201);
        $supplierId = $response->json('data.id');

        // Retrieve the supplier
        $showResponse = $this->actingAs($this->user)->getJson("/api/supplier/suppliers/{$supplierId}");
        $showResponse->assertOk();

        $retrieved = $showResponse->json('data');

        // Verify all provided fields match
        foreach ($data as $field => $value) {
            $this->assertEquals($value, $retrieved[$field], "Field '{$field}' mismatch on round-trip");
        }
    }

    /**
     * Property 1 (update path): Update → retrieve returns updated fields.
     *
     * **Validates: Requirements 1.1, 1.2**
     */
    public function test_property1_update_round_trip(): void
    {
        $supplier = SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Original Name',
            'phone' => '08111111111',
        ]);

        $updates = [
            'name' => 'Updated Name',
            'phone' => '08222222222',
            'email' => 'updated@example.com',
            'bank_name' => 'BRI',
            'bank_account_number' => '5555666677',
            'bank_account_holder' => 'New Holder',
        ];

        $this->actingAs($this->user)->putJson("/api/supplier/suppliers/{$supplier->id}", $updates)
            ->assertOk();

        $showResponse = $this->actingAs($this->user)->getJson("/api/supplier/suppliers/{$supplier->id}");
        $retrieved = $showResponse->json('data');

        foreach ($updates as $field => $value) {
            $this->assertEquals($value, $retrieved[$field], "Updated field '{$field}' not persisted");
        }
    }

    // =========================================================================
    // Property 2: Soft-delete preserves PO history
    // Validates: Requirements 1.3
    // =========================================================================

    /**
     * Property 2: For any supplier with POs, soft-delete preserves PO data.
     *
     * **Validates: Requirements 1.3**
     */
    public function test_property2_soft_delete_preserves_po_history(): void
    {
        $supplier = SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Supplier To Delete',
        ]);

        // Create multiple POs for this supplier
        $poData = [
            ['total' => 100000, 'status' => 'confirmed'],
            ['total' => 200000, 'status' => 'received'],
            ['total' => 50000, 'status' => 'draft'],
        ];

        $poIds = [];
        foreach ($poData as $i => $pd) {
            $po = PurchaseOrderModel::create([
                'outlet_id' => $this->outlet->id,
                'supplier_id' => $supplier->id,
                'po_number' => "PO-20260801-" . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'order_date' => '2026-08-01',
                'status' => $pd['status'],
                'payment_status' => 'unpaid',
                'total_amount' => $pd['total'],
            ]);
            $poIds[] = $po->id;

            PurchaseOrderItemModel::create([
                'purchase_order_id' => $po->id,
                'product_variant_id' => 1,
                'product_name' => "Product {$i}",
                'variant_name' => 'Default',
                'quantity' => 10,
                'unit_cost' => $pd['total'] / 10,
                'subtotal' => $pd['total'],
                'received_quantity' => 0,
            ]);
        }

        // Soft-delete the supplier
        $this->actingAs($this->user)->deleteJson("/api/supplier/suppliers/{$supplier->id}")
            ->assertStatus(204);

        // Verify supplier is soft-deleted
        $this->assertSoftDeleted('supplier_suppliers', ['id' => $supplier->id]);

        // Verify ALL PO data remains intact
        foreach ($poIds as $idx => $poId) {
            $this->assertDatabaseHas('supplier_purchase_orders', [
                'id' => $poId,
                'supplier_id' => $supplier->id,
                'total_amount' => $poData[$idx]['total'],
                'status' => $poData[$idx]['status'],
            ]);
        }

        // Verify PO items remain intact
        foreach ($poIds as $poId) {
            $this->assertDatabaseHas('supplier_purchase_order_items', [
                'purchase_order_id' => $poId,
            ]);
        }
    }

    // =========================================================================
    // Property 3: Search returns all matching, excludes non-matching
    // Validates: Requirements 1.4
    // =========================================================================

    /**
     * Property 3: Search completeness — all matching suppliers returned, non-matching excluded.
     *
     * **Validates: Requirements 1.4**
     */
    public function test_property3_search_completeness_by_name(): void
    {
        $matching = [
            SupplierModel::create(['outlet_id' => $this->outlet->id, 'name' => 'PT Maju Jaya']),
            SupplierModel::create(['outlet_id' => $this->outlet->id, 'name' => 'CV Maju Terus']),
            SupplierModel::create(['outlet_id' => $this->outlet->id, 'name' => 'Toko maju Bersama']),
        ];

        $nonMatching = [
            SupplierModel::create(['outlet_id' => $this->outlet->id, 'name' => 'PT Sentosa']),
            SupplierModel::create(['outlet_id' => $this->outlet->id, 'name' => 'CV Abadi']),
        ];

        $response = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/suppliers/search?q=maju"
        );

        $response->assertOk();
        $results = collect($response->json('data'));

        // All matching suppliers found
        foreach ($matching as $supplier) {
            $this->assertTrue(
                $results->contains('id', $supplier->id),
                "Matching supplier '{$supplier->name}' not found in search results"
            );
        }

        // No non-matching suppliers in results
        foreach ($nonMatching as $supplier) {
            $this->assertFalse(
                $results->contains('id', $supplier->id),
                "Non-matching supplier '{$supplier->name}' incorrectly included"
            );
        }
    }

    public function test_property3_search_completeness_by_phone(): void
    {
        $matching = [
            SupplierModel::create(['outlet_id' => $this->outlet->id, 'name' => 'Supplier A', 'phone' => '08129991111']),
            SupplierModel::create(['outlet_id' => $this->outlet->id, 'name' => 'Supplier B', 'phone' => '08129992222']),
        ];

        $nonMatching = [
            SupplierModel::create(['outlet_id' => $this->outlet->id, 'name' => 'Supplier C', 'phone' => '08555555555']),
        ];

        $response = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/suppliers/search?q=081299"
        );

        $response->assertOk();
        $results = collect($response->json('data'));

        foreach ($matching as $supplier) {
            $this->assertTrue($results->contains('id', $supplier->id));
        }
        foreach ($nonMatching as $supplier) {
            $this->assertFalse($results->contains('id', $supplier->id));
        }
    }

    public function test_property3_search_completeness_by_email(): void
    {
        $matching = [
            SupplierModel::create(['outlet_id' => $this->outlet->id, 'name' => 'S1', 'email' => 'admin@domain.co.id']),
            SupplierModel::create(['outlet_id' => $this->outlet->id, 'name' => 'S2', 'email' => 'info@domain.co.id']),
        ];

        $nonMatching = [
            SupplierModel::create(['outlet_id' => $this->outlet->id, 'name' => 'S3', 'email' => 'hello@other.com']),
        ];

        $response = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/suppliers/search?q=domain.co"
        );

        $response->assertOk();
        $results = collect($response->json('data'));

        foreach ($matching as $supplier) {
            $this->assertTrue($results->contains('id', $supplier->id));
        }
        foreach ($nonMatching as $supplier) {
            $this->assertFalse($results->contains('id', $supplier->id));
        }
    }

    // =========================================================================
    // Property 4: Name uniqueness within outlet
    // Validates: Requirements 1.6
    // =========================================================================

    public static function duplicateNameProvider(): array
    {
        return [
            'exact same name' => ['PT Makmur Sentosa', 'PT Makmur Sentosa'],
            'short name' => ['ABC', 'ABC'],
            'with special chars' => ['Supplier & Co.', 'Supplier & Co.'],
        ];
    }

    /**
     * Property 4: For any name, second creation with same name in same outlet fails.
     *
     * **Validates: Requirements 1.6**
     */
    #[DataProvider('duplicateNameProvider')]
    public function test_property4_name_uniqueness_same_outlet(string $firstName, string $secondName): void
    {
        // Create first supplier
        $this->actingAs($this->user)->postJson(
            "/api/supplier/outlets/{$this->outlet->id}/suppliers",
            ['name' => $firstName]
        )->assertStatus(201);

        // Attempt duplicate
        $response = $this->actingAs($this->user)->postJson(
            "/api/supplier/outlets/{$this->outlet->id}/suppliers",
            ['name' => $secondName]
        );

        $response->assertStatus(422);
    }

    /**
     * Property 4 (cross-outlet): Same name in different outlet is allowed.
     *
     * **Validates: Requirements 1.6**
     */
    public function test_property4_name_allowed_across_outlets(): void
    {
        $otherOutlet = OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Other Outlet',
            'business_type' => 'kafe',
        ]);

        $name = 'PT Universal Supplier';

        // Create in first outlet
        $this->actingAs($this->user)->postJson(
            "/api/supplier/outlets/{$this->outlet->id}/suppliers",
            ['name' => $name]
        )->assertStatus(201);

        // Create same name in second outlet — should succeed
        $this->actingAs($this->user)->postJson(
            "/api/supplier/outlets/{$otherOutlet->id}/suppliers",
            ['name' => $name]
        )->assertStatus(201);
    }

    // =========================================================================
    // Property 5: Outlet scoping isolation
    // Validates: Requirements 1.7
    // =========================================================================

    /**
     * Property 5: Suppliers in outlet A never appear in queries for outlet B.
     *
     * **Validates: Requirements 1.7**
     */
    public function test_property5_outlet_isolation_listing(): void
    {
        $outletB = OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Outlet B',
            'business_type' => 'kafe',
        ]);

        // Create suppliers in outlet A
        $supplierA1 = SupplierModel::create(['outlet_id' => $this->outlet->id, 'name' => 'Supplier A1']);
        $supplierA2 = SupplierModel::create(['outlet_id' => $this->outlet->id, 'name' => 'Supplier A2']);

        // Create suppliers in outlet B
        $supplierB1 = SupplierModel::create(['outlet_id' => $outletB->id, 'name' => 'Supplier B1']);
        $supplierB2 = SupplierModel::create(['outlet_id' => $outletB->id, 'name' => 'Supplier B2']);

        // Query outlet A — only A suppliers
        $responseA = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/suppliers"
        );
        $responseA->assertOk();
        $idsA = collect($responseA->json('data'))->pluck('id')->all();

        $this->assertContains($supplierA1->id, $idsA);
        $this->assertContains($supplierA2->id, $idsA);
        $this->assertNotContains($supplierB1->id, $idsA);
        $this->assertNotContains($supplierB2->id, $idsA);

        // Query outlet B — only B suppliers
        $responseB = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$outletB->id}/suppliers"
        );
        $responseB->assertOk();
        $idsB = collect($responseB->json('data'))->pluck('id')->all();

        $this->assertContains($supplierB1->id, $idsB);
        $this->assertContains($supplierB2->id, $idsB);
        $this->assertNotContains($supplierA1->id, $idsB);
        $this->assertNotContains($supplierA2->id, $idsB);
    }

    /**
     * Property 5 (search isolation): Search only returns from queried outlet.
     *
     * **Validates: Requirements 1.7**
     */
    public function test_property5_outlet_isolation_search(): void
    {
        $outletB = OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Outlet B',
            'business_type' => 'kafe',
        ]);

        SupplierModel::create(['outlet_id' => $this->outlet->id, 'name' => 'Universal Supplier']);
        SupplierModel::create(['outlet_id' => $outletB->id, 'name' => 'Universal Supplier B']);

        // Search "Universal" in outlet A
        $response = $this->actingAs($this->user)->getJson(
            "/api/supplier/outlets/{$this->outlet->id}/suppliers/search?q=Universal"
        );
        $response->assertOk();
        $results = collect($response->json('data'));

        $this->assertEquals(1, $results->count());
        $this->assertEquals('Universal Supplier', $results->first()['name']);
    }
}
