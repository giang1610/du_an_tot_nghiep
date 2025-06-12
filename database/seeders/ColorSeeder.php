<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColorSeeder extends Seeder
{
    public function run()
    {
        $colors = [
            ['name' => 'Red', 'image' => 'colors/red.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Blue', 'image' => 'colors/blue.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Green', 'image' => 'colors/green.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Yellow', 'image' => 'colors/yellow.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Black', 'image' => 'colors/black.png', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('colors')->insert($colors);
    }
}
