<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Pos\Infrastructure\Models\DiscountModel;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Pos\Infrastructure\Models\ProductModel;
use Modules\Pos\Infrastructure\Models\VoucherModel;

class PosDiscountVoucherSeeder extends Seeder
{
    public function run(): void
    {
        $outlet = OutletModel::first();

        if (!$outlet) {
            $this->command->error('Outlet belum ada. Jalankan PosSeeder terlebih dahulu.');
            return;
        }

        DB::transaction(function () use ($outlet) {
            $this->createDiscounts($outlet);
            $this->command->info('✓ Discounts created');

            $this->createVouchers($outlet);
            $this->command->info('✓ Vouchers created');

            $this->command->info('Discount & Voucher seeding complete!');
        });
    }

    private function createDiscounts(OutletModel $outlet): void
    {
        $now = Carbon::now();

        // Ambil beberapa produk untuk diskon per-produk
        $products = ProductModel::where('outlet_id', $outlet->id)->get();
        $latte = $products->firstWhere('name', 'Latte');
        $esKopiSusu = $products->firstWhere('name', 'Es Kopi Susu');
        $croissant = $products->firstWhere('name', 'Croissant');
        $cappuccino = $products->firstWhere('name', 'Cappuccino');
        $nasiGoreng = $products->firstWhere('name', 'Nasi Goreng');

        $discounts = [
            // === DISKON UMUM (berlaku seluruh transaksi) ===
            [
                'name' => 'Diskon Weekend 10%',
                'type' => 'percentage',
                'value' => 10,
                'min_purchase' => 50000,
                'member_only' => false,
                'is_active' => true,
                'priority' => 1,
                'starts_at' => $now->copy()->next('Saturday'),
                'ends_at' => $now->copy()->next('Sunday')->endOfDay(),
                'conditions' => ['days' => ['Saturday', 'Sunday']],
                'product_id' => null,
            ],
            [
                'name' => 'Member Discount 15%',
                'type' => 'percentage',
                'value' => 15,
                'min_purchase' => 75000,
                'member_only' => true,
                'is_active' => true,
                'priority' => 2,
                'starts_at' => $now->copy()->startOfMonth(),
                'ends_at' => $now->copy()->endOfMonth(),
                'conditions' => null,
                'product_id' => null,
            ],
            [
                'name' => 'Hemat Rp 10.000',
                'type' => 'fixed',
                'value' => 10000,
                'min_purchase' => 100000,
                'member_only' => false,
                'is_active' => true,
                'priority' => 3,
                'starts_at' => $now->copy()->startOfMonth(),
                'ends_at' => $now->copy()->endOfMonth(),
                'conditions' => null,
                'product_id' => null,
            ],
            [
                'name' => 'Happy Hour 20% (14:00-17:00)',
                'type' => 'percentage',
                'value' => 20,
                'min_purchase' => null,
                'member_only' => false,
                'is_active' => true,
                'priority' => 4,
                'starts_at' => $now->copy()->subDays(3),
                'ends_at' => $now->copy()->addDays(30),
                'conditions' => ['hours' => ['start' => '14:00', 'end' => '17:00']],
                'product_id' => null,
            ],
            [
                'name' => 'Diskon Pelajar 10%',
                'type' => 'percentage',
                'value' => 10,
                'min_purchase' => 30000,
                'member_only' => true,
                'is_active' => true,
                'priority' => 5,
                'starts_at' => $now->copy()->startOfMonth(),
                'ends_at' => $now->copy()->addMonths(2)->endOfMonth(),
                'conditions' => ['segment' => 'student'],
                'product_id' => null,
            ],

            // === DISKON PER-PRODUK ===
            [
                'name' => 'Promo Latte Rp 5.000 OFF',
                'type' => 'fixed',
                'value' => 5000,
                'min_purchase' => null,
                'member_only' => false,
                'is_active' => true,
                'priority' => 10,
                'starts_at' => $now->copy()->subDays(2),
                'ends_at' => $now->copy()->addDays(14),
                'conditions' => null,
                'product_id' => $latte?->id,
            ],
            [
                'name' => 'Es Kopi Susu 20% OFF',
                'type' => 'percentage',
                'value' => 20,
                'min_purchase' => null,
                'member_only' => false,
                'is_active' => true,
                'priority' => 11,
                'starts_at' => $now->copy()->subDays(1),
                'ends_at' => $now->copy()->addDays(7),
                'conditions' => null,
                'product_id' => $esKopiSusu?->id,
            ],
            [
                'name' => 'Cappuccino Beli 2 Gratis 1',
                'type' => 'percentage',
                'value' => 33,
                'min_purchase' => null,
                'member_only' => false,
                'is_active' => true,
                'priority' => 12,
                'starts_at' => $now->copy()->subDays(1),
                'ends_at' => $now->copy()->addDays(10),
                'conditions' => ['min_qty' => 3],
                'product_id' => $cappuccino?->id,
            ],
            [
                'name' => 'Nasi Goreng Spesial Rp 8.000 OFF',
                'type' => 'fixed',
                'value' => 8000,
                'min_purchase' => null,
                'member_only' => false,
                'is_active' => true,
                'priority' => 13,
                'starts_at' => $now->copy(),
                'ends_at' => $now->copy()->addDays(21),
                'conditions' => null,
                'product_id' => $nasiGoreng?->id,
            ],

            // === DISKON SUDAH EXPIRED (untuk testing) ===
            [
                'name' => 'Flash Sale Croissant 50%',
                'type' => 'percentage',
                'value' => 50,
                'min_purchase' => null,
                'member_only' => false,
                'is_active' => true,
                'priority' => 20,
                'starts_at' => $now->copy()->subDays(10),
                'ends_at' => $now->copy()->subDays(3),
                'conditions' => null,
                'product_id' => $croissant?->id,
            ],
            [
                'name' => 'Grand Opening 30%',
                'type' => 'percentage',
                'value' => 30,
                'min_purchase' => null,
                'member_only' => false,
                'is_active' => false,
                'priority' => 21,
                'starts_at' => $now->copy()->subMonths(2),
                'ends_at' => $now->copy()->subMonths(1),
                'conditions' => null,
                'product_id' => null,
            ],
        ];

        foreach ($discounts as $data) {
            DiscountModel::create(array_merge($data, [
                'outlet_id' => $outlet->id,
            ]));
        }
    }

    private function createVouchers(OutletModel $outlet): void
    {
        $now = Carbon::now();

        // Ambil beberapa produk untuk voucher per-produk
        $products = ProductModel::where('outlet_id', $outlet->id)->get();
        $latte = $products->firstWhere('name', 'Latte');
        $matcha = $products->firstWhere('name', 'Matcha Latte');
        $nasiGoreng = $products->firstWhere('name', 'Nasi Goreng');
        $americano = $products->firstWhere('name', 'Americano');
        $smoothie = $products->firstWhere('name', 'Smoothie Berry');

        $vouchers = [
            // === VOUCHER UMUM ===
            [
                'code' => 'WELCOME20',
                'type' => 'percentage',
                'value' => 20,
                'min_purchase' => 50000,
                'usage_limit' => 100,
                'usage_count' => 12,
                'expires_at' => $now->copy()->addMonths(3),
                'is_active' => true,
                'product_id' => null,
            ],
            [
                'code' => 'HEMAT10K',
                'type' => 'fixed',
                'value' => 10000,
                'min_purchase' => 75000,
                'usage_limit' => 50,
                'usage_count' => 8,
                'expires_at' => $now->copy()->addMonth(),
                'is_active' => true,
                'product_id' => null,
            ],
            [
                'code' => 'JUMAT15',
                'type' => 'percentage',
                'value' => 15,
                'min_purchase' => 40000,
                'usage_limit' => 200,
                'usage_count' => 45,
                'expires_at' => $now->copy()->addMonths(2),
                'is_active' => true,
                'product_id' => null,
            ],
            [
                'code' => 'GRATIS25K',
                'type' => 'fixed',
                'value' => 25000,
                'min_purchase' => 150000,
                'usage_limit' => 25,
                'usage_count' => 3,
                'expires_at' => $now->copy()->addDays(45),
                'is_active' => true,
                'product_id' => null,
            ],
            [
                'code' => 'NEWUSER50',
                'type' => 'percentage',
                'value' => 50,
                'min_purchase' => 30000,
                'usage_limit' => 500,
                'usage_count' => 127,
                'expires_at' => $now->copy()->addMonths(6),
                'is_active' => true,
                'product_id' => null,
            ],

            // === VOUCHER PER-PRODUK ===
            [
                'code' => 'LATTEFREE',
                'type' => 'fixed',
                'value' => 10000,
                'min_purchase' => null,
                'usage_limit' => 30,
                'usage_count' => 5,
                'expires_at' => $now->copy()->addDays(30),
                'is_active' => true,
                'product_id' => $latte?->id,
            ],
            [
                'code' => 'MATCHA25',
                'type' => 'percentage',
                'value' => 25,
                'min_purchase' => null,
                'usage_limit' => 20,
                'usage_count' => 3,
                'expires_at' => $now->copy()->addDays(14),
                'is_active' => true,
                'product_id' => $matcha?->id,
            ],
            [
                'code' => 'NASGOR5K',
                'type' => 'fixed',
                'value' => 5000,
                'min_purchase' => null,
                'usage_limit' => null,
                'usage_count' => 0,
                'expires_at' => $now->copy()->addDays(60),
                'is_active' => true,
                'product_id' => $nasiGoreng?->id,
            ],
            [
                'code' => 'AMERICANO30',
                'type' => 'percentage',
                'value' => 30,
                'min_purchase' => null,
                'usage_limit' => 50,
                'usage_count' => 11,
                'expires_at' => $now->copy()->addDays(21),
                'is_active' => true,
                'product_id' => $americano?->id,
            ],
            [
                'code' => 'SMOOTHIE10K',
                'type' => 'fixed',
                'value' => 10000,
                'min_purchase' => null,
                'usage_limit' => 15,
                'usage_count' => 2,
                'expires_at' => $now->copy()->addDays(14),
                'is_active' => true,
                'product_id' => $smoothie?->id,
            ],

            // === VOUCHER EXPIRED (untuk testing) ===
            [
                'code' => 'EXPIRED50',
                'type' => 'percentage',
                'value' => 50,
                'min_purchase' => null,
                'usage_limit' => 10,
                'usage_count' => 10,
                'expires_at' => $now->copy()->subDays(7),
                'is_active' => true,
                'product_id' => null,
            ],
            [
                'code' => 'OLDPROMO',
                'type' => 'fixed',
                'value' => 15000,
                'min_purchase' => 50000,
                'usage_limit' => 30,
                'usage_count' => 30,
                'expires_at' => $now->copy()->subDays(14),
                'is_active' => true,
                'product_id' => null,
            ],

            // === VOUCHER NON-AKTIF (untuk testing) ===
            [
                'code' => 'INACTIVE15',
                'type' => 'percentage',
                'value' => 15,
                'min_purchase' => 30000,
                'usage_limit' => 100,
                'usage_count' => 0,
                'expires_at' => $now->copy()->addMonths(2),
                'is_active' => false,
                'product_id' => null,
            ],
            [
                'code' => 'DISABLED20K',
                'type' => 'fixed',
                'value' => 20000,
                'min_purchase' => 80000,
                'usage_limit' => 50,
                'usage_count' => 0,
                'expires_at' => $now->copy()->addMonth(),
                'is_active' => false,
                'product_id' => null,
            ],
        ];

        foreach ($vouchers as $data) {
            VoucherModel::create(array_merge($data, [
                'outlet_id' => $outlet->id,
            ]));
        }
    }
}
