<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SizeSeeder extends Seeder
{
    public function run()

    {
        DB::table('sizes')->delete();
        $sizes = [
            ['name' => 'S (45-50kg)', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'M (51-60kg)', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'L (61-70kg)', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'XL (71-80kg)', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'XXL (81-90kg)', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '3XL (91-100kg)', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '4XL (101-120kg)', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '5XL (121-130kg)', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '6XL (131-140kg)', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '7XL (141-150kg)', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('sizes')->insert($sizes);
    }
}
