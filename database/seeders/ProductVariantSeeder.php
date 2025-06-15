<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ProductVariantSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Giả sử bạn đã có product_id, color_id, size_id từ các bảng tương ứng
        $productIds = DB::table('products')->pluck('id')->toArray();
        $colorIds = DB::table('colors')->pluck('id')->toArray();
        $sizeIds = DB::table('sizes')->pluck('id')->toArray();

        for ($i = 0; $i < 50; $i++) {
            DB::table('product_variants')->insert([
                'product_id'       => $faker->randomElement($productIds),
                'sku'              => strtoupper($faker->bothify('SKU-###??')),
                'price'            => $faker->randomFloat(2, 10, 200), // giá từ 10 đến 200
                'sale_price'       => null, // mặc định chưa giảm giá
                'sale_start_date'  => null,
                'sale_end_date'    => null,
                'stock'            => $faker->numberBetween(0, 100),
                'color_id'         => $faker->randomElement($colorIds),
                'size_id'          => $faker->randomElement($sizeIds),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }
}
