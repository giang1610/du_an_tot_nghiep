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
            ['name' => 'Orange', 'image' => 'colors/orange.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pink', 'image' => 'colors/pink.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Brown', 'image' => 'colors/brown.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Purple', 'image' => 'colors/purple.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'White', 'image' => 'colors/white.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gray', 'image' => 'colors/gray.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cyan', 'image' => 'colors/cyan.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Magenta', 'image' => 'colors/magenta.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lime', 'image' => 'colors/lime.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Teal', 'image' => 'colors/teal.png', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('colors')->insert($colors);
    }
}
