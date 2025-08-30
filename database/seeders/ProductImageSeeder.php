<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductImageSeeder extends Seeder
{
    public function run()
    {
        // Lấy sản phẩm theo tên
        $product = DB::table('products')->where('name', 'Áo kiểu nữ tay lỡ')->first();

        // Chỉ chạy nếu tìm thấy sản phẩm
        if ($product) {
            // Lấy 2 biến thể đầu tiên của sản phẩm này
            $variant1 = DB::table('product_variants')->where('product_id', $product->id)->first();
            $variant2 = DB::table('product_variants')->where('product_id', $product->id)->skip(1)->first();

            // Chèn dữ liệu vào bảng product_images
            DB::table('product_images')->insert([
                [
                    'url' => 'products/2.jpg',
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'is_default' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'url' => 'variants/1.jpg',
                    'product_id' => $product->id,
                    'product_variant_id' => $variant1?->id,
                    'is_default' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'url' => 'variants/2.jpg',
                    'product_id' => $product->id,
                    'product_variant_id' => $variant2?->id,
                    'is_default' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
