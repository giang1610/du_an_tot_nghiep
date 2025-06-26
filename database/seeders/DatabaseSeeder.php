<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\ProductVariantSeeder;
use Database\Seeders\ProductVariantOptionSeeder;
use Database\Seeders\ProductImageSeeder;


class DatabaseSeeder extends Seeder
{
        public function run()
        {
                $this->call([
<<<<<<< HEAD
                        // CategorySeeder::class,
                        // ProductSeeder::class,
                        // ColorSeeder::class,
                        // SizeSeeder::class,
                        // ProductVariantSeeder::class,
                        ProductImageSeeder::class,
                        // ProductSeeder::class,
                        // ProductVariantSeeder::class,
                        // ProductImageSeeder::class,
=======
                        CategorySeeder::class,
                        ProductSeeder::class,
                        // ProductVariantSeeder::class,
                        // ProductImageSeeder::class,
                        ColorSeeder::class,
                        SizeSeeder::class,
>>>>>>> dc51002ee5daa226bdc0f197f1663ce3246aaa0b
                        // AdminUserSeeder::class
                ]);
        }
}
