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


     }
}
