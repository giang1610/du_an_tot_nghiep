<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variantIds = DB::table('product_variants')->pluck('id')->toArray();

        foreach ($variantIds as $variantId) {
            DB::table('stocks')->insert([
                'product_variant_id' => $variantId,
                'quantity' => rand(0, 100),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
