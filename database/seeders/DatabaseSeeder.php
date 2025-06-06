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
        CategorySeeder::class,
        ColorSeeder::class,
        SizeSeeder::class,
        ProductSeeder::class,
        ProductVariantSeeder::class,
        ProductImageSeeder::class,
    ]);

}

}
