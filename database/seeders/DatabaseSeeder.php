<?php

namespace Database\Seeders;

use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            QuoteSeeder::class,
            // HolidaySeeder::class,
            PosSeeder::class,
            SupplierSeeder::class,
            PosDiscountVoucherSeeder::class,
        ]);
    }
}
