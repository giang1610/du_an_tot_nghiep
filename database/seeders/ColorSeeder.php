<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColorSeeder extends Seeder
{
    public function run()
    {
        $colors = [
            ['name' => 'Red', 'value' => 'colors/red.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Blue', 'value' => 'colors/blue.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Green', 'value' => 'colors/green.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Yellow', 'value' => 'colors/yellow.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Black', 'value' => 'colors/black.png', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('colors')->insert($colors);
    }
}
