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
        'id' => 3,
        'name' => 'Phụ kiện',
        'slug' => Str::slug('Phụ kiện'),
        'status' => 0,
    ],
    ]);
    }
}
