<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Pos\Infrastructure\Models\ProductModel;
use Modules\Pos\Infrastructure\Models\ProductVariantModel;
use Modules\Supplier\Infrastructure\Models\GoodsReceiptItemModel;
use Modules\Supplier\Infrastructure\Models\GoodsReceiptModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderItemModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderModel;
use Modules\Supplier\Infrastructure\Models\SupplierModel;
use Modules\Supplier\Infrastructure\Models\SupplierPaymentModel;
use Modules\Supplier\Infrastructure\Models\SupplierProductModel;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $outlet = OutletModel::where('name', 'Kopi Nusantara')->first();

        if (!$outlet) {
            $this->command->error('Outlet "Kopi Nusantara" not found. Please run PosSeeder first.');
            return;
        }

        if (SupplierModel::where('outlet_id', $outlet->id)->exists()) {
            $this->command->info('Supplier data already exists, skipping...');
            return;
        }

        DB::transaction(function () use ($outlet) {
            $this->command->info('Seeding Supplier data...');

            // 1. Create Suppliers
            $suppliers = $this->createSuppliers($outlet);
            $this->command->info('✓ 5 suppliers created');

            // 2. Link Supplier Products
            $supplierProducts = $this->linkSupplierProducts($suppliers, $outlet);
            $this->command->info('✓ Supplier-product links created');

            // 3. Create Purchase Orders
            $purchaseOrders = $this->createPurchaseOrders($outlet, $suppliers, $supplierProducts);
            $this->command->info('✓ Purchase orders created');

            // 4. Create Goods Receipts
            $this->createGoodsReceipts($purchaseOrders);
            $this->command->info('✓ Goods receipts created');

            // 5. Create Payments
            $this->createPayments($purchaseOrders);
            $this->command->info('✓ Payments created');

            $this->command->info('Supplier seeding complete!');
        });
    }

    private function createSuppliers(OutletModel $outlet): array
    {
        $supplierData = [
            [
                'name' => 'PT Kopi Sejahtera',
                'address' => 'Jl. Raya Bogor Km 30, Cimanggis, Depok, Jawa Barat 16451',
                'phone' => '021-8712345',
                'email' => 'order@kopisejahtera.co.id',
                'bank_name' => 'BCA',
                'bank_account_number' => '1234567890',
                'bank_account_holder' => 'PT Kopi Sejahtera',
                'notes' => 'Supplier biji kopi arabika dan robusta',
            ],
            [
                'name' => 'CV Teh Nusantara',
                'address' => 'Jl. Puncak No. 45, Cisarua, Bogor, Jawa Barat 16750',
                'phone' => '0251-8234567',
                'email' => 'sales@tehnusantara.com',
                'bank_name' => 'Mandiri',
                'bank_account_number' => '1300012345678',
                'bank_account_holder' => 'CV Teh Nusantara',
                'notes' => 'Supplier teh hijau, teh hitam, dan matcha',
            ],
            [
                'name' => 'UD Sumber Pangan',
                'address' => 'Jl. Pasar Minggu Raya No. 12, Jakarta Selatan 12520',
                'phone' => '021-7891234',
                'email' => 'info@sumberpangan.id',
                'bank_name' => 'BRI',
                'bank_account_number' => '0012345678901',
                'bank_account_holder' => 'Hadi Supriyanto',
                'notes' => 'Supplier bahan makanan (mie, beras, bumbu)',
            ],
            [
                'name' => 'PT Dairy Fresh',
                'address' => 'Jl. Industri Raya Blok C5, Tangerang, Banten 15135',
                'phone' => '021-5567890',
                'email' => 'purchasing@dairyfresh.co.id',
                'bank_name' => 'BCA',
                'bank_account_number' => '0987654321',
                'bank_account_holder' => 'PT Dairy Fresh Indonesia',
                'notes' => 'Supplier susu segar, cream, dan produk dairy',
            ],
            [
                'name' => 'Toko Bahan Kue Makmur',
                'address' => 'Jl. Fatmawati No. 88, Cipete, Jakarta Selatan 12410',
                'phone' => '021-7654321',
                'email' => 'tbk.makmur@gmail.com',
                'bank_name' => 'BNI',
                'bank_account_number' => '0123456789',
                'bank_account_holder' => 'Lim Siew Lan',
                'notes' => 'Supplier tepung, cokelat, selai, dan bahan kue',
            ],
        ];

        $suppliers = [];
        foreach ($supplierData as $data) {
            $suppliers[] = SupplierModel::create(array_merge($data, [
                'outlet_id' => $outlet->id,
            ]));
        }

        return $suppliers;
    }

    private function linkSupplierProducts(array $suppliers, OutletModel $outlet): array
    {
        // Get products by category
        $products = ProductModel::where('outlet_id', $outlet->id)
            ->with('variants')
            ->get()
            ->groupBy(function ($product) {
                return $product->category_id;
            });

        $categoryMap = \Modules\Pos\Infrastructure\Models\CategoryModel::where('outlet_id', $outlet->id)
            ->pluck('id', 'name')
            ->toArray();

        // Supplier → category mapping
        $supplierCategoryLinks = [
            0 => ['Kopi'],                          // PT Kopi Sejahtera
            1 => ['Teh'],                           // CV Teh Nusantara
            2 => ['Makanan Berat', 'Makanan Ringan'], // UD Sumber Pangan
            3 => ['Non-Kopi', 'Minuman Dingin'],    // PT Dairy Fresh
            4 => ['Makanan Ringan'],                // Toko Bahan Kue Makmur
        ];

        $supplierProducts = [];

        foreach ($supplierCategoryLinks as $supplierIndex => $categoryNames) {
            $supplier = $suppliers[$supplierIndex];

            foreach ($categoryNames as $categoryName) {
                if (!isset($categoryMap[$categoryName])) {
                    continue;
                }

                $categoryId = $categoryMap[$categoryName];
                $categoryProducts = $products->get($categoryId, collect());

                foreach ($categoryProducts as $product) {
                    foreach ($product->variants as $variant) {
                        $sellingPrice = (float) $variant->price;
                        // Unit cost is 50-70% of selling price
                        $costPercentage = rand(50, 70) / 100;
                        $unitCost = round($sellingPrice * $costPercentage / 100) * 100; // Round to nearest 100

                        $sp = SupplierProductModel::create([
                            'supplier_id' => $supplier->id,
                            'product_variant_id' => $variant->id,
                            'default_unit_cost' => $unitCost,
                        ]);

                        $supplierProducts[$supplier->id][] = [
                            'supplier_product' => $sp,
                            'variant' => $variant,
                            'product' => $product,
                            'unit_cost' => $unitCost,
                        ];
                    }
                }
            }
        }

        return $supplierProducts;
    }

    private function createPurchaseOrders(OutletModel $outlet, array $suppliers, array $supplierProducts): array
    {
        $now = Carbon::now();
        $purchaseOrders = [];
        $poCounter = 1;

        // Define PO configurations: [supplier_index, status, payment_status, days_ago]
        $poConfigs = [
            // 2 draft POs
            ['supplier' => 0, 'status' => 'draft', 'payment_status' => 'unpaid', 'days_ago' => 3],
            ['supplier' => 2, 'status' => 'draft', 'payment_status' => 'unpaid', 'days_ago' => 1],
            // 3 confirmed POs
            ['supplier' => 1, 'status' => 'confirmed', 'payment_status' => 'unpaid', 'days_ago' => 15],
            ['supplier' => 0, 'status' => 'confirmed', 'payment_status' => 'partial', 'days_ago' => 20],
            ['supplier' => 3, 'status' => 'confirmed', 'payment_status' => 'paid', 'days_ago' => 25],
            // 2 partial (partially received)
            ['supplier' => 2, 'status' => 'partial', 'payment_status' => 'partial', 'days_ago' => 35],
            ['supplier' => 4, 'status' => 'partial', 'payment_status' => 'unpaid', 'days_ago' => 30],
            // 1 received (fully received + fully paid)
            ['supplier' => 0, 'status' => 'received', 'payment_status' => 'paid', 'days_ago' => 50],
        ];

        foreach ($poConfigs as $config) {
            $supplier = $suppliers[$config['supplier']];
            $date = $now->copy()->subDays($config['days_ago']);
            $poNumber = 'PO-' . $date->format('Ymd') . '-' . str_pad($poCounter++, 3, '0', STR_PAD_LEFT);

            // Get available products for this supplier
            $availableProducts = $supplierProducts[$supplier->id] ?? [];
            if (empty($availableProducts)) {
                continue;
            }

            // Pick 2-5 items
            $itemCount = min(rand(2, 5), count($availableProducts));
            $selectedKeys = array_rand($availableProducts, $itemCount);
            if (!is_array($selectedKeys)) {
                $selectedKeys = [$selectedKeys];
            }

            $totalAmount = 0;
            $poItems = [];

            foreach ($selectedKeys as $key) {
                $sp = $availableProducts[$key];
                $qty = rand(5, 50);
                $unitCost = $sp['unit_cost'];
                $subtotal = $unitCost * $qty;
                $totalAmount += $subtotal;

                $poItems[] = [
                    'product_variant_id' => $sp['variant']->id,
                    'product_name' => $sp['product']->name,
                    'variant_name' => $sp['variant']->name,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                    'received_quantity' => 0, // Will be updated by goods receipts
                ];
            }

            $po = PurchaseOrderModel::create([
                'outlet_id' => $outlet->id,
                'supplier_id' => $supplier->id,
                'po_number' => $poNumber,
                'order_date' => $date,
                'expected_delivery_date' => $date->copy()->addDays(rand(5, 14)),
                'status' => $config['status'],
                'payment_status' => $config['payment_status'],
                'total_amount' => $totalAmount,
                'notes' => $this->generatePoNotes($config['status']),
            ]);

            $createdItems = [];
            foreach ($poItems as $item) {
                $createdItems[] = PurchaseOrderItemModel::create(array_merge($item, [
                    'purchase_order_id' => $po->id,
                ]));
            }

            $purchaseOrders[] = [
                'model' => $po,
                'items' => $createdItems,
                'config' => $config,
            ];
        }

        return $purchaseOrders;
    }

    private function createGoodsReceipts(array &$purchaseOrders): void
    {
        foreach ($purchaseOrders as &$po) {
            $status = $po['config']['status'];

            if ($status === 'partial') {
                // Partial receipt: receive ~50% of each item
                $receipt = GoodsReceiptModel::create([
                    'purchase_order_id' => $po['model']->id,
                    'receipt_date' => Carbon::parse($po['model']->order_date)->addDays(rand(5, 10)),
                    'notes' => 'Pengiriman parsial batch 1',
                ]);

                foreach ($po['items'] as $item) {
                    $receivedQty = (int) ceil($item->quantity * 0.5);

                    GoodsReceiptItemModel::create([
                        'goods_receipt_id' => $receipt->id,
                        'purchase_order_item_id' => $item->id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity' => $receivedQty,
                    ]);

                    // Update received_quantity on PO item
                    $item->update(['received_quantity' => $receivedQty]);

                    // Update stock on product variant
                    ProductVariantModel::where('id', $item->product_variant_id)
                        ->increment('stock_quantity', $receivedQty);
                }
            } elseif ($status === 'received') {
                // Full receipt: receive 100% of each item
                $receipt = GoodsReceiptModel::create([
                    'purchase_order_id' => $po['model']->id,
                    'receipt_date' => Carbon::parse($po['model']->order_date)->addDays(rand(7, 14)),
                    'notes' => 'Pengiriman lengkap diterima',
                ]);

                foreach ($po['items'] as $item) {
                    GoodsReceiptItemModel::create([
                        'goods_receipt_id' => $receipt->id,
                        'purchase_order_item_id' => $item->id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity' => $item->quantity,
                    ]);

                    // Update received_quantity on PO item
                    $item->update(['received_quantity' => $item->quantity]);

                    // Update stock on product variant
                    ProductVariantModel::where('id', $item->product_variant_id)
                        ->increment('stock_quantity', $item->quantity);
                }
            }
        }
    }

    private function createPayments(array $purchaseOrders): void
    {
        $paymentMethods = ['cash', 'bank_transfer', 'e_wallet'];

        foreach ($purchaseOrders as $po) {
            $paymentStatus = $po['config']['payment_status'];
            $totalAmount = (float) $po['model']->total_amount;

            if ($paymentStatus === 'partial') {
                // 1-2 payments covering ~50% of total
                $paymentCount = rand(1, 2);
                $targetAmount = $totalAmount * 0.5;

                if ($paymentCount === 1) {
                    SupplierPaymentModel::create([
                        'purchase_order_id' => $po['model']->id,
                        'amount' => round($targetAmount / 1000) * 1000,
                        'payment_date' => Carbon::parse($po['model']->order_date)->addDays(rand(1, 5)),
                        'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                        'notes' => 'Pembayaran DP 50%',
                    ]);
                } else {
                    $firstPayment = round(($targetAmount * 0.6) / 1000) * 1000;
                    $secondPayment = round($targetAmount / 1000) * 1000 - $firstPayment;

                    SupplierPaymentModel::create([
                        'purchase_order_id' => $po['model']->id,
                        'amount' => $firstPayment,
                        'payment_date' => Carbon::parse($po['model']->order_date)->addDays(rand(1, 3)),
                        'payment_method' => 'bank_transfer',
                        'notes' => 'Pembayaran tahap 1',
                    ]);

                    SupplierPaymentModel::create([
                        'purchase_order_id' => $po['model']->id,
                        'amount' => $secondPayment,
                        'payment_date' => Carbon::parse($po['model']->order_date)->addDays(rand(4, 7)),
                        'payment_method' => 'bank_transfer',
                        'notes' => 'Pembayaran tahap 2',
                    ]);
                }
            } elseif ($paymentStatus === 'paid') {
                // Full payment
                SupplierPaymentModel::create([
                    'purchase_order_id' => $po['model']->id,
                    'amount' => $totalAmount,
                    'payment_date' => Carbon::parse($po['model']->order_date)->addDays(rand(1, 7)),
                    'payment_method' => 'bank_transfer',
                    'notes' => 'Pembayaran lunas',
                ]);
            }
        }
    }

    private function generatePoNotes(string $status): ?string
    {
        return match ($status) {
            'draft' => 'Menunggu konfirmasi harga dari supplier',
            'confirmed' => 'Sudah dikonfirmasi, menunggu pengiriman',
            'partial' => 'Sebagian barang sudah diterima',
            'received' => 'Semua barang sudah diterima dengan baik',
            default => null,
        };
    }
}
