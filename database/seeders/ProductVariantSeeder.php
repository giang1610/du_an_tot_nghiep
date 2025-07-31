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
        $imageIndex = 1;
        $maxImages = 20;

        foreach ($productIds as $productId) {
            $variantColors = $faker->randomElements($colorIds, rand(2, 5)); // mỗi sản phẩm 2-5 màu
            foreach ($variantColors as $colorId) {
                $variantSizes = $faker->randomElements($sizeIds, rand(2, 4)); // mỗi màu 2-4 size
                // Ảnh cho mỗi màu
                $currentImageNumber = (($imageIndex - 1) % $maxImages) + 1;
                $imagePath = 'variants/' . $currentImageNumber . '.jpg';
                foreach ($variantSizes as $sizeId) {
                    $combinationKey = $productId . '-' . $colorId . '-' . $sizeId;
                    if (isset($usedCombinations[$combinationKey]))
                        continue;
                    $usedCombinations[$combinationKey] = true;
                    DB::table('product_variants')->insert([
                        'product_id' => $productId,
                        'color_id' => $colorId,
                        'size_id' => $sizeId,
                        'price' => $faker->randomElement([99000, 199000, 249000, 299000, 349000, 399000]),
                        'sku' => strtoupper(uniqid('SKU_')),
                        'image' => $imagePath,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $imageIndex++;
            }
        }
    }
}
