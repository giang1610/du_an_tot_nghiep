<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductVariantOptionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('product_variant_options')->insert([
            // Biến thể 1 có 2 thuộc tính
            [
                'product_variant_id' => 1,
                'attribute_name' => 'Màu sắc',
                'attribute_value' => 'Đỏ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_variant_id' => 1,
                'attribute_name' => 'Kích cỡ',
                'attribute_value' => 'M',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Biến thể 2 có 2 thuộc tính
            [
                'product_variant_id' => 2,
                'attribute_name' => 'Màu sắc',
                'attribute_value' => 'Xanh',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_variant_id' => 2,
                'attribute_name' => 'Kích cỡ',
                'attribute_value' => 'L',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
