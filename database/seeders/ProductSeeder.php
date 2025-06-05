<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    public function run()
    {
<<<<<<< HEAD

=======
    
>>>>>>> test
        // 2. Sản phẩm
        DB::table('products')->insert([
            [
                'id' => null,
                'category_id' => 3,
                'name' => 'Áo thun cotton 100%',
                'slug' => Str::slug('Áo thun cotton 100%'),
                // 'type' => 1,
                'status' => 1,
                'short_description' => 'Áo thun mát mẻ, thoải mái.',
                'description' => 'Chất liệu cotton 100%, mềm mại và co giãn tốt.',
                'thumbnail' => 'products/ao-thun.jpg'
            ]
        ]);

<<<<<<< HEAD
        // 3. Biến thể sản phẩm
    //     DB::table('product_variants')->insert([
    //         [
    //             'id' => 1,
    //             'product_id' => 1,
    //             'sku' => 'ATCOTTON-M',
    //             'price' => 150000,
    //             'sale_price' => 140000,
    //             'stock' => 50,
    //             'sale_start' => Carbon::now()->subDays(3),
    //             'sale_end' => Carbon::now()->addDays(7),
    //         ]
    //     ]);

    //     // 4. Thuộc tính biến thể
    //     DB::table('product_variant_options')->insert([
    //         [
    //             'product_variant_id' => 1,
    //             'name' => 'Màu sắc',
    //             'value' => 'Đỏ'
    //         ],
    //         [
    //             'product_variant_id' => 1,
    //             'name' => 'Kích cỡ',
    //             'value' => 'M'
    //         ]
    //     ]);

    //     // 5. Hình ảnh sản phẩm và biến thể
    //     DB::table('product_images')->insert([
    //         [
    //             'url' => 'products/ao-thun-cotton.jpg',
    //             'product_id' => 1,
    //             'product_variant_id' => null,
    //             'is_default' => 1
    //         ],
    //         [
    //             'url' => 'products/ao-thun-cotton-m.jpg',
    //             'product_id' => 1,
    //             'product_variant_id' => 1,
    //             'is_default' => 0
    //         ]
    //     ]);
=======

>>>>>>> test
     }
}
