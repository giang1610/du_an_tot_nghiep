<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProductVariantSeeder extends Seeder
{
    public function run()
    {
        // Xóa dữ liệu ở bảng con trước để tránh lỗi foreign key
        DB::table('product_variant_options')->delete();
        DB::table('product_variants')->delete();

        // Dữ liệu mẫu
        $variants = [
            [
                'product_id'     => 1,
                'sku'            => 'ATCOTTON-M',
                'price'          => 150000,
                'sale_price'     => 140000,
                'sale_start_date'=> Carbon::now()->subDays(5),
                'sale_end_date'  => Carbon::now()->addDays(5),
                'stock'          => 50,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'product_id'     => 1,
                'sku'            => 'ATCOTTON-L',
                'price'          => 150000,
                'sale_price'     => null,
                'sale_start_date'=> null,
                'sale_end_date'  => null,
                'stock'          => 30,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'product_id'     => 2,
                'sku'            => 'JEANS-32',
                'price'          => 300000,
                'sale_price'     => 270000,
                'sale_start_date'=> Carbon::now()->subDays(3),
                'sale_end_date'  => Carbon::now()->addDays(7),
                'stock'          => 40,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ];

        // Chèn dữ liệu vào bảng
        DB::table('product_variants')->insert($variants);
    }
}
