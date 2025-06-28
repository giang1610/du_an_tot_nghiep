<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class ProductImageSeeder extends Seeder
{
    public function run()
    {
        $product = Product::first(); // lấy sản phẩm đầu tiên
        if (!$product) {
            $this->command->warn('⚠️ Không có sản phẩm nào để thêm ảnh.');
            return;
        }

        // Ảnh chính của sản phẩm
        DB::table('product_images')->insert([
            [
                'product_id' => $product->id,
                'product_variant_id' => null,
                'url' => '/images/product-main.jpg',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Ảnh cho các biến thể (variants)
        $variants = ProductVariant::where('product_id', $product->id)->get();

        foreach ($variants as $index => $variant) {
            DB::table('product_images')->insert([
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'url' => "/images/product-variant{$index}.jpg",
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ Seed hình ảnh sản phẩm thành công.');
    }
}
