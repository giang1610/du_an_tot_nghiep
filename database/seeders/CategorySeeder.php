<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // //  DB::table('categories')->delete();
        //   DB::table('categories')->truncate(); // Xóa dữ liệu cũ, reset id

        DB::table('categories')->insert([
            [
                'id' => 1,
                'name' => 'Thời trang nam',
                'slug' => Str::slug('Thời trang nam'),
                'status' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Thời trang nữ',
                'slug' => Str::slug('Thời trang nữ'),
                'status' => 1,
            ],
            
            [
                'id' => 4,
                'name' => 'váy đầm',
                'slug' => Str::slug('váy đầm'),
                'status' => 1,
            ],
            [
                'id' => 5,
                'name' => 'Đồ thể thao',
                'slug' => Str::slug('Đồ thể thao'),
                'status' => 1,
            ],
            [
                'id' => 6,
                'name' => 'Đồ ngủ',
                'slug' => Str::slug('Đồ ngủ'),
                'status' => 1,
            ],
            [
                'id' => 7,
                'name' => 'Đồ bơi',
                'slug' => Str::slug('Đồ bơi'),
                'status' => 1,
            ],
            [
                'id' => 8,
                'name' => 'Đồ công sở',
                'slug' => Str::slug('Đồ công sở'),
                'status' => 1,
            ],
            [
                'id' => 9,
                'name' => 'Đồ trẻ em',
                'slug' => Str::slug('Đồ trẻ em'),
                'status' => 1,
            ],
            [
                'id' => 10,
                'name' => 'Áo khoác',
                'slug' => Str::slug('Áo khoác'),
                'status' => 1,
            ],
            [
                'id' => 11,
                'name' => 'Quần jeans',
                'slug' => Str::slug('Quần jeans'),
                'status' => 1,
            ],
            [
                'id' => 12,
                'name' => 'Áo sơ mi',
                'slug' => Str::slug('Áo sơ mi'),
                'status' => 1,
            ],
            [
                'id' => 13,
                'name' => 'Áo thun',
                'slug' => Str::slug('Áo thun'),
                'status' => 1,
            ],
            [
                'id' => 14,
                'name' => 'Áo mùa đông',
                'slug' => Str::slug('Áo mùa đông'),
                'status' => 1,
            ],
            [
                'id' => 15,
                'name' => 'Quần tây',
                'slug' => Str::slug('Quần tây'),
                'status' => 1,
            ],
            [
                'id' => 16,
                'name' => 'Quần short',
                'slug' => Str::slug('Quần short'),
                'status' => 1,
            ],
            [
                'id' => 17,
                'name' => 'Áo polo',
                'slug' => Str::slug('Áo polo'),
                'status' => 1,
            ],
            [
                'id' => 18,
                'name' => 'Áo vest',
                'slug' => Str::slug('Áo vest'),
                'status' => 1,
            ],
            [
                'id' => 19,
                'name' => 'Đồ lót nam',
                'slug' => Str::slug('Đồ lót nam'),
                'status' => 1,
            ],
            [
                'id' => 20,
                'name' => 'Đồ lót nữ',
                'slug' => Str::slug('Đồ lót nữ'),
                'status' => 1,
            ],
        ]);
    }
}
