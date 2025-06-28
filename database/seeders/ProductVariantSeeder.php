<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductVariant;
use Illuminate\Support\Carbon;

class ProductVariantSeeder extends Seeder
{
    public function run()
    {
        // Giả sử bạn đã có product_id = 1, color_id = 1~2, size_id = 1~3
        $variants = [
            [
                'product_id' => 1,
                'color_id' => 1,
                'size_id' => 1,
                'price' => 500000,
                'sale_price' => 450000,
                'sale_start_date' => Carbon::now()->subDays(1),
                'sale_end_date' => Carbon::now()->addDays(3),
                'image' => '/images/product1-variant1.jpg',
                'stock' => 10,
            ],
            [
                'product_id' => 1,
                'color_id' => 1,
                'size_id' => 2,
                'price' => 500000,
                'sale_price' => null,
                'sale_start_date' => null,
                'sale_end_date' => null,
                'image' => '/images/product1-variant2.jpg',
                'stock' => 5,
            ],
            [
                'product_id' => 1,
                'color_id' => 2,
                'size_id' => 1,
                'price' => 520000,
                'sale_price' => 480000,
                'sale_start_date' => Carbon::now()->subDays(2),
                'sale_end_date' => Carbon::now()->addDays(1),
                'image' => '/images/product1-variant3.jpg',
                'stock' => 7,
            ],
        ];

        foreach ($variants as $variant) {
            // Tự động tạo SKU theo dạng: P001-C1-S1
            $variant['sku'] = 'P' . str_pad($variant['product_id'], 3, '0', STR_PAD_LEFT)
                            . '-C' . $variant['color_id']
                            . '-S' . $variant['size_id'];

            ProductVariant::create($variant);
        }
    }
}
