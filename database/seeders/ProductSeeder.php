<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'name' => 'Giày di bo Nam',
            'slug' => 'giay di bo nam',
            'category_id' => 1,
            'price' => 1200000,
            'description' => 'Đẹp'
        ]);

        Product::create([
            'name' => 'Giày da Nữ',
            'slug' => 'Giay da nu',
            'category_id' => 1,
            'price' => 1500000,
            'description' => 'Đẹp'
        ]);
         Product::create([
            'name' => 'Giày Thể Thao Nữ',
            'slug' => 'giay the thao nu',
            'category_id' => 1,
            'price' => 1500000,
            'description' => 'Đẹp'
        ]);
         Product::create([
            'name' => 'Giày Nike Nam',
            'slug' => 'Giay nike nam',
            'category_id' => 1,
            'price' => 1500000,
            'description' => 'Đẹp'
        ]);
         Product::create([
            'name' => 'Giày the thao nam',
            'slug' => 'Giay the thao nam',
            'category_id' => 1,
            'price' => 1500000,
            'description' => 'Đẹp'
        ]);
         Product::create([
            'name' => 'Giày đi bộ Nữ',
            'slug' => 'Giay di bo nu',
            'category_id' => 1,
            'price' => 1500000,
            'description' => 'Đẹp'
        ]);

        // Thêm sản phẩm khác nếu cần
    }
}
