<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\ProductVariantSeeder;
use Database\Seeders\ProductImageSeeder;
use Database\Seeders\ColorSeeder;
use Database\Seeders\SizeSeeder;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\VouchersTableSeeder;
use Database\Seeders\StockSeeder;


class DatabaseSeeder extends Seeder
{
        public function run()
        {
                $this->call([
                        // CategorySeeder::class,
                        // ProductSeeder::class,
                        // ProductVariantSeeder::class,
                        // ProductImageSeeder::class,
                        // ColorSeeder::class,
                        // SizeSeeder::class,
                        // AdminUserSeeder::class,
                        // VouchersTableSeeder::class,
                        StockSeeder::class,
                ]);
        }
}
