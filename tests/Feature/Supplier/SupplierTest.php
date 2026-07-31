<?php

namespace Tests\Feature\Supplier;

use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderModel;
use Modules\Supplier\Infrastructure\Models\SupplierModel;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
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

    public function test_can_create_supplier(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/supplier/outlets/{$this->outlet->id}/suppliers", [
            'name' => 'PT Sumber Makmur',
            'address' => 'Jl. Industri No. 5',
            'phone' => '08123456789',
            'email' => 'info@sumbermakmur.co.id',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'PT Sumber Makmur',
            'notes' => 'Supplier bahan pokok',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'PT Sumber Makmur')
            ->assertJsonPath('data.phone', '08123456789')
            ->assertJsonPath('data.email', 'info@sumbermakmur.co.id')
            ->assertJsonPath('data.bank_name', 'BCA')
            ->assertJsonPath('data.bank_account_number', '1234567890')
            ->assertJsonPath('data.bank_account_holder', 'PT Sumber Makmur')
            ->assertJsonPath('data.notes', 'Supplier bahan pokok');

        $this->assertDatabaseHas('supplier_suppliers', [
            'outlet_id' => $this->outlet->id,
            'name' => 'PT Sumber Makmur',
            'phone' => '08123456789',
        ]);
    }

    public function test_can_list_suppliers_paginated(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            SupplierModel::create([
                'outlet_id' => $this->outlet->id,
                'name' => "Supplier {$i}",
            ]);
        }

        $response = $this->actingAs($this->user)->getJson("/api/supplier/outlets/{$this->outlet->id}/suppliers?per_page=10");

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.current_page', 1);
    }

    public function test_can_update_supplier(): void
    {
        $supplier = SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Old Name',
            'phone' => '08111111111',
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/supplier/suppliers/{$supplier->id}", [
            'name' => 'New Name',
            'phone' => '08222222222',
            'email' => 'new@example.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.phone', '08222222222')
            ->assertJsonPath('data.email', 'new@example.com');

        $this->assertDatabaseHas('supplier_suppliers', [
            'id' => $supplier->id,
            'name' => 'New Name',
            'phone' => '08222222222',
        ]);
    }

    public function test_can_soft_delete_supplier(): void
    {
        $supplier = SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'To Delete',
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/supplier/suppliers/{$supplier->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('supplier_suppliers', ['id' => $supplier->id]);

        // Supplier should not appear in list
        $listResponse = $this->actingAs($this->user)->getJson("/api/supplier/outlets/{$this->outlet->id}/suppliers");
        $listResponse->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_rejects_duplicate_name_in_same_outlet(): void
    {
        SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Duplicate Supplier',
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/supplier/outlets/{$this->outlet->id}/suppliers", [
            'name' => 'Duplicate Supplier',
        ]);

        $response->assertStatus(422);
    }

    public function test_allows_same_name_in_different_outlet(): void
    {
        $otherOutlet = OutletModel::create([
            'user_id' => $this->user->id,
            'name' => 'Other Outlet',
            'business_type' => 'kafe',
        ]);

        SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Same Name Supplier',
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/supplier/outlets/{$otherOutlet->id}/suppliers", [
            'name' => 'Same Name Supplier',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Same Name Supplier');
    }

    public function test_search_by_name(): void
    {
        SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'PT Maju Jaya',
            'phone' => '08111111111',
        ]);
        SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'CV Sentosa',
            'phone' => '08222222222',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/supplier/outlets/{$this->outlet->id}/suppliers/search?q=Maju");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'PT Maju Jaya');
    }

    public function test_search_by_phone(): void
    {
        SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Supplier A',
            'phone' => '08999123456',
        ]);
        SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Supplier B',
            'phone' => '08111222333',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/supplier/outlets/{$this->outlet->id}/suppliers/search?q=08999");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Supplier A');
    }

    public function test_search_by_email(): void
    {
        SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Supplier Email A',
            'email' => 'contact@supplierA.com',
        ]);
        SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Supplier Email B',
            'email' => 'info@supplierB.com',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/supplier/outlets/{$this->outlet->id}/suppliers/search?q=supplierA");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Supplier Email A');
    }

    public function test_show_includes_total_debt(): void
    {
        $supplier = SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Supplier With Debt',
        ]);

        // Create a confirmed PO with total_amount to simulate debt
        PurchaseOrderModel::create([
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $supplier->id,
            'po_number' => 'PO-20260801-001',
            'order_date' => '2026-08-01',
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'total_amount' => 500000,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/supplier/suppliers/{$supplier->id}");

        $response->assertOk()
            ->assertJsonPath('data.name', 'Supplier With Debt')
            ->assertJsonPath('data.total_debt', 500000);
    }

    public function test_outlet_scoping(): void
    {
        $otherUser = User::factory()->create();
        $otherOutlet = OutletModel::create([
            'user_id' => $otherUser->id,
            'name' => 'Other User Outlet',
            'business_type' => 'retail',
        ]);

        SupplierModel::create([
            'outlet_id' => $otherOutlet->id,
            'name' => 'Other Outlet Supplier',
        ]);

        SupplierModel::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'My Outlet Supplier',
        ]);

        // User should only see suppliers from their own outlet
        $response = $this->actingAs($this->user)->getJson("/api/supplier/outlets/{$this->outlet->id}/suppliers");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'My Outlet Supplier');

        // User should not be able to access other user's outlet suppliers
        $response = $this->actingAs($this->user)->getJson("/api/supplier/outlets/{$otherOutlet->id}/suppliers");

        $response->assertStatus(403);
    }
}
