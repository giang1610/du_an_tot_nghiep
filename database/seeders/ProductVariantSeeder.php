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

        $productIds = DB::table('products')->pluck('id')->toArray();
        $colorIds = DB::table('colors')->pluck('id')->toArray();
        $sizeIds = DB::table('sizes')->pluck('id')->toArray();

        $usedCombinations = [];

        foreach ($productIds as $productId) {
            $variantCount = rand(2, 5); // mỗi sản phẩm có từ 2 đến 5 biến thể

            for ($i = 0; $i < $variantCount; $i++) {
                $colorId = $faker->randomElement($colorIds);
                $sizeId = $faker->randomElement($sizeIds);
                $combinationKey = $productId . '-' . $colorId . '-' . $sizeId;

                // Kiểm tra nếu biến thể đã tồn tại thì bỏ qua
                if (isset($usedCombinations[$combinationKey])) {
                    $i--; // lặp lại vòng lặp để bù lại biến thể trùng
                    continue;
                }

                $usedCombinations[$combinationKey] = true;

                DB::table('product_variants')->insert([
                    'product_id' => $productId,
                    'color_id' => $colorId,
                    'size_id' => $sizeId,
                    'price' => $faker->randomElement([99000, 199000, 249000, 299000, 349000, 399000]),
                    'sku' => strtoupper(uniqid('SKU_')),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
