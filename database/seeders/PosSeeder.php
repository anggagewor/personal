<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Pos\Infrastructure\Models\CategoryModel;
use Modules\Pos\Infrastructure\Models\OutletModel;
use Modules\Pos\Infrastructure\Models\PaymentMethodModel;
use Modules\Pos\Infrastructure\Models\ProductModel;
use Modules\Pos\Infrastructure\Models\ProductVariantModel;
use Modules\Pos\Infrastructure\Models\TransactionItemModel;
use Modules\Pos\Infrastructure\Models\TransactionModel;
use Modules\User\Infrastructure\Models\UserModel;

class PosSeeder extends Seeder
{
    public function run(): void
    {
        if (OutletModel::where('name', 'Kopi Nusantara')->exists()) {
            $this->command->info('POS data already exists, skipping...');
            return;
        }

        DB::transaction(function () {
            $this->command->info('Seeding POS data...');

            $user = UserModel::firstOrCreate(
                ['email' => 'test@example.com'],
                ['name' => 'Test User', 'password' => bcrypt('password')]
            );

            // 1. Create Outlet
            $outlet = OutletModel::create([
                'user_id' => $user->id,
                'name' => 'Kopi Nusantara',
                'business_type' => 'kafe',
                'payment_flow' => 'both',
                'address' => 'Jl. Sudirman No. 123, Senopati, Jakarta Selatan 12190',
                'phone' => '021-5551234',
                'settings' => [
                    'currency' => 'IDR',
                    'tax_rate' => 0,
                    'receipt_footer' => 'Terima kasih telah berkunjung!',
                ],
            ]);
            $this->command->info('✓ Outlet created: Kopi Nusantara');

            // 2. Create Categories
            $categories = $this->createCategories($outlet);
            $this->command->info('✓ 6 categories created');

            // 3. Create Products with Variants
            $products = $this->createProducts($outlet, $categories);
            $this->command->info('✓ ' . count($products) . ' products created with variants');

            // 4. Create Payment Methods
            $paymentMethods = $this->createPaymentMethods($outlet);
            $this->command->info('✓ 4 payment methods created');

            // 5. Create Transactions
            $this->createTransactions($outlet, $products, $paymentMethods);
            $this->command->info('✓ Transactions created');

            $this->command->info('POS seeding complete!');
        });
    }

    private function createCategories(OutletModel $outlet): array
    {
        $categoryData = [
            ['name' => 'Kopi', 'icon' => 'coffee', 'sort_order' => 1],
            ['name' => 'Teh', 'icon' => 'leaf', 'sort_order' => 2],
            ['name' => 'Non-Kopi', 'icon' => 'cup-soda', 'sort_order' => 3],
            ['name' => 'Makanan Ringan', 'icon' => 'cookie', 'sort_order' => 4],
            ['name' => 'Makanan Berat', 'icon' => 'utensils', 'sort_order' => 5],
            ['name' => 'Minuman Dingin', 'icon' => 'snowflake', 'sort_order' => 6],
        ];

        $categories = [];
        foreach ($categoryData as $data) {
            $categories[$data['name']] = CategoryModel::create(array_merge($data, [
                'outlet_id' => $outlet->id,
            ]));
        }

        return $categories;
    }

    private function createProducts(OutletModel $outlet, array $categories): array
    {
        $productDefinitions = [
            // Kopi
            [
                'category' => 'Kopi',
                'name' => 'Espresso',
                'base_price' => 18000,
                'has_variants' => true,
                'track_stock' => false,
                'variants' => [
                    ['name' => 'Single Shot', 'price' => 18000, 'stock' => 0],
                    ['name' => 'Double Shot', 'price' => 25000, 'stock' => 0],
                ],
            ],
            [
                'category' => 'Kopi',
                'name' => 'Americano',
                'base_price' => 22000,
                'has_variants' => true,
                'track_stock' => false,
                'variants' => [
                    ['name' => 'Hot', 'price' => 22000, 'stock' => 0],
                    ['name' => 'Iced', 'price' => 25000, 'stock' => 0],
                ],
            ],
            [
                'category' => 'Kopi',
                'name' => 'Latte',
                'base_price' => 28000,
                'has_variants' => true,
                'track_stock' => false,
                'variants' => [
                    ['name' => 'Hot', 'price' => 28000, 'stock' => 0],
                    ['name' => 'Iced', 'price' => 30000, 'stock' => 0],
                    ['name' => 'Large', 'price' => 35000, 'stock' => 0],
                ],
            ],
            [
                'category' => 'Kopi',
                'name' => 'Cappuccino',
                'base_price' => 28000,
                'has_variants' => true,
                'track_stock' => false,
                'variants' => [
                    ['name' => 'Hot', 'price' => 28000, 'stock' => 0],
                    ['name' => 'Iced', 'price' => 30000, 'stock' => 0],
                ],
            ],
            [
                'category' => 'Kopi',
                'name' => 'V60',
                'base_price' => 32000,
                'has_variants' => true,
                'track_stock' => false,
                'variants' => [
                    ['name' => 'Arabika Gayo', 'price' => 35000, 'stock' => 0],
                    ['name' => 'Robusta Lampung', 'price' => 32000, 'stock' => 0],
                ],
            ],
            // Teh
            [
                'category' => 'Teh',
                'name' => 'Teh Tarik',
                'base_price' => 20000,
                'has_variants' => false,
                'track_stock' => false,
                'variants' => [
                    ['name' => 'Regular', 'price' => 20000, 'stock' => 0],
                ],
            ],
            [
                'category' => 'Teh',
                'name' => 'Teh Hijau',
                'base_price' => 18000,
                'has_variants' => false,
                'track_stock' => false,
                'variants' => [
                    ['name' => 'Regular', 'price' => 18000, 'stock' => 0],
                ],
            ],
            [
                'category' => 'Teh',
                'name' => 'Matcha Latte',
                'base_price' => 30000,
                'has_variants' => true,
                'track_stock' => false,
                'variants' => [
                    ['name' => 'Hot', 'price' => 30000, 'stock' => 0],
                    ['name' => 'Iced', 'price' => 32000, 'stock' => 0],
                ],
            ],
            // Non-Kopi
            [
                'category' => 'Non-Kopi',
                'name' => 'Cokelat Panas',
                'base_price' => 25000,
                'has_variants' => true,
                'track_stock' => false,
                'variants' => [
                    ['name' => 'Regular', 'price' => 25000, 'stock' => 0],
                    ['name' => 'Large', 'price' => 32000, 'stock' => 0],
                ],
            ],
            [
                'category' => 'Non-Kopi',
                'name' => 'Susu Segar',
                'base_price' => 20000,
                'has_variants' => true,
                'track_stock' => false,
                'variants' => [
                    ['name' => 'Plain', 'price' => 20000, 'stock' => 0],
                    ['name' => 'Cokelat', 'price' => 22000, 'stock' => 0],
                    ['name' => 'Strawberry', 'price' => 22000, 'stock' => 0],
                ],
            ],
            // Makanan Ringan
            [
                'category' => 'Makanan Ringan',
                'name' => 'Croissant',
                'base_price' => 25000,
                'has_variants' => true,
                'track_stock' => true,
                'variants' => [
                    ['name' => 'Plain', 'price' => 25000, 'stock' => 30],
                    ['name' => 'Cokelat', 'price' => 28000, 'stock' => 25],
                ],
            ],
            [
                'category' => 'Makanan Ringan',
                'name' => 'Roti Bakar',
                'base_price' => 20000,
                'has_variants' => true,
                'track_stock' => true,
                'variants' => [
                    ['name' => 'Cokelat', 'price' => 20000, 'stock' => 40],
                    ['name' => 'Keju', 'price' => 22000, 'stock' => 35],
                    ['name' => 'Selai Kacang', 'price' => 20000, 'stock' => 30],
                ],
            ],
            [
                'category' => 'Makanan Ringan',
                'name' => 'Kentang Goreng',
                'base_price' => 22000,
                'has_variants' => false,
                'track_stock' => true,
                'variants' => [
                    ['name' => 'Regular', 'price' => 22000, 'stock' => 50],
                ],
            ],
            // Makanan Berat
            [
                'category' => 'Makanan Berat',
                'name' => 'Nasi Goreng',
                'base_price' => 35000,
                'has_variants' => false,
                'track_stock' => true,
                'variants' => [
                    ['name' => 'Regular', 'price' => 35000, 'stock' => 40],
                ],
            ],
            [
                'category' => 'Makanan Berat',
                'name' => 'Mie Goreng',
                'base_price' => 32000,
                'has_variants' => false,
                'track_stock' => true,
                'variants' => [
                    ['name' => 'Regular', 'price' => 32000, 'stock' => 35],
                ],
            ],
            [
                'category' => 'Makanan Berat',
                'name' => 'Indomie Spesial',
                'base_price' => 25000,
                'has_variants' => false,
                'track_stock' => true,
                'variants' => [
                    ['name' => 'Regular', 'price' => 25000, 'stock' => 60],
                ],
            ],
            // Minuman Dingin
            [
                'category' => 'Minuman Dingin',
                'name' => 'Jus Jeruk',
                'base_price' => 20000,
                'has_variants' => false,
                'track_stock' => false,
                'variants' => [
                    ['name' => 'Regular', 'price' => 20000, 'stock' => 0],
                ],
            ],
            [
                'category' => 'Minuman Dingin',
                'name' => 'Jus Mangga',
                'base_price' => 22000,
                'has_variants' => false,
                'track_stock' => false,
                'variants' => [
                    ['name' => 'Regular', 'price' => 22000, 'stock' => 0],
                ],
            ],
            [
                'category' => 'Minuman Dingin',
                'name' => 'Smoothie Berry',
                'base_price' => 30000,
                'has_variants' => false,
                'track_stock' => false,
                'variants' => [
                    ['name' => 'Regular', 'price' => 30000, 'stock' => 0],
                ],
            ],
            [
                'category' => 'Minuman Dingin',
                'name' => 'Es Kopi Susu',
                'base_price' => 25000,
                'has_variants' => false,
                'track_stock' => false,
                'variants' => [
                    ['name' => 'Regular', 'price' => 25000, 'stock' => 0],
                ],
            ],
        ];

        $products = [];
        $skuCounter = 1;

        foreach ($productDefinitions as $def) {
            $category = $categories[$def['category']];
            $sku = 'KN-' . str_pad($skuCounter++, 3, '0', STR_PAD_LEFT);

            $product = ProductModel::create([
                'outlet_id' => $outlet->id,
                'category_id' => $category->id,
                'name' => $def['name'],
                'base_price' => $def['base_price'],
                'sku' => $sku,
                'has_variants' => $def['has_variants'],
                'track_stock' => $def['track_stock'],
                'status' => 'active',
            ]);

            $variantModels = [];
            $variantCounter = 1;
            foreach ($def['variants'] as $variant) {
                $variantSku = $sku . '-' . str_pad($variantCounter++, 2, '0', STR_PAD_LEFT);
                $variantModels[] = ProductVariantModel::create([
                    'product_id' => $product->id,
                    'name' => $variant['name'],
                    'sku' => $variantSku,
                    'price' => $variant['price'],
                    'stock_quantity' => $variant['stock'],
                ]);
            }

            $products[] = [
                'model' => $product,
                'variants' => $variantModels,
                'category' => $def['category'],
            ];
        }

        return $products;
    }

    private function createPaymentMethods(OutletModel $outlet): array
    {
        $methods = [
            [
                'type' => 'cash',
                'name' => 'Tunai',
                'is_active' => true,
                'sort_order' => 1,
                'settings' => [],
            ],
            [
                'type' => 'qris',
                'name' => 'QRIS',
                'is_active' => true,
                'sort_order' => 2,
                'settings' => ['provider' => 'Dana'],
            ],
            [
                'type' => 'bank_transfer',
                'name' => 'Transfer Bank',
                'is_active' => true,
                'sort_order' => 3,
                'settings' => ['bank' => 'BCA', 'account_number' => '1234567890'],
            ],
            [
                'type' => 'e_wallet',
                'name' => 'GoPay',
                'is_active' => true,
                'sort_order' => 4,
                'settings' => ['provider' => 'GoPay'],
            ],
        ];

        $paymentMethods = [];
        foreach ($methods as $method) {
            $paymentMethods[] = PaymentMethodModel::create(array_merge($method, [
                'outlet_id' => $outlet->id,
            ]));
        }

        return $paymentMethods;
    }

    private function createTransactions(OutletModel $outlet, array $products, array $paymentMethods): void
    {
        $now = Carbon::now();
        $transactionCount = 18;
        $paymentTypes = ['cash', 'qris', 'bank_transfer', 'e_wallet'];
        $paymentNames = ['Tunai', 'QRIS', 'Transfer Bank', 'GoPay'];

        for ($i = 1; $i <= $transactionCount; $i++) {
            $daysAgo = rand(0, 29);
            $date = $now->copy()->subDays($daysAgo);
            $transactionNumber = 'TRX-' . $date->format('Ymd') . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);

            $paymentIndex = array_rand($paymentTypes);
            $isVoided = ($i === 15); // Make transaction #15 voided

            // Pick 2-5 random items
            $itemCount = rand(2, 5);
            $selectedProducts = array_rand($products, min($itemCount, count($products)));
            if (!is_array($selectedProducts)) {
                $selectedProducts = [$selectedProducts];
            }

            $subtotal = 0;
            $items = [];

            foreach ($selectedProducts as $prodIndex) {
                $product = $products[$prodIndex];
                $variant = $product['variants'][array_rand($product['variants'])];
                $qty = rand(1, 3);
                $unitPrice = (float) $variant->price;
                $itemSubtotal = $unitPrice * $qty;
                $subtotal += $itemSubtotal;

                $items[] = [
                    'product_id' => $product['model']->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $product['model']->name,
                    'variant_name' => $variant->name,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $total = $subtotal;
            $amountTendered = $total;
            $change = 0;

            // For cash payments, round up
            if ($paymentTypes[$paymentIndex] === 'cash') {
                $amountTendered = ceil($total / 5000) * 5000;
                $change = $amountTendered - $total;
            }

            $transaction = TransactionModel::create([
                'outlet_id' => $outlet->id,
                'transaction_number' => $transactionNumber,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'total' => $total,
                'payment_method' => $paymentNames[$paymentIndex],
                'payment_method_type' => $paymentTypes[$paymentIndex],
                'amount_tendered' => $amountTendered,
                'change_amount' => $change,
                'status' => $isVoided ? 'voided' : 'completed',
                'void_reason' => $isVoided ? 'Pesanan salah input' : null,
                'voided_at' => $isVoided ? $date->copy()->addMinutes(5) : null,
                'notes' => null,
                'created_at' => $date->copy()->setHour(rand(8, 21))->setMinute(rand(0, 59)),
                'updated_at' => $date->copy()->setHour(rand(8, 21))->setMinute(rand(0, 59)),
            ]);

            foreach ($items as $item) {
                TransactionItemModel::create(array_merge($item, [
                    'transaction_id' => $transaction->id,
                    'created_at' => $transaction->created_at,
                ]));
            }
        }
    }
}
