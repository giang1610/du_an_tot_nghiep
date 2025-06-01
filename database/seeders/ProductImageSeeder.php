<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('product_images')->insert([
            // Ảnh cho sản phẩm ID = 1
            [
                'url' => 'products/ao-thun-1.jpg',
                'product_id' => 1,
                'product_variant_id' => null,
                'is_default' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'url' => 'products/ao-thun-2.jpg',
                'product_id' => 1,
                'product_variant_id' => null,
                'is_default' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Ảnh cho biến thể sản phẩm ID = 1
            [
                'url' => 'variants/ao-thun-red-m.jpg',
                'product_id' => null,
                'product_variant_id' => null,
                'is_default' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Ảnh cho biến thể sản phẩm ID = 2
            [
                'url' => 'variants/ao-thun-blue-l.jpg',
                'product_id' => null,
                'product_variant_id' => null,
                'is_default' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
