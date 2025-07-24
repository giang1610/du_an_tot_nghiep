<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VouchersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('vouchers')->insert([
            [
                'name' => 'Giảm 10% cho đơn hàng ',
                'code' => 'SAVE10',
                'type' => 'product',
                'discount_type' => 'percent',
                'discount_amount' => null,
                'discount_percent' => 10,
                'start_date' => '2025-06-01',
                'end_date' => '2025-12-31',
                'quantity' => 200,
                'usage_limit' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Giảm 10.000 cho ship',
                'code' => 'SHIP10K',
                'type' => 'shipping',
                'discount_type' => 'amount',
                'discount_amount' => 50000,
                'discount_percent' => null,
                'start_date' => '2025-08-01',
                'end_date' => '2025-12-31',
                'quantity' => 50,
                'usage_limit' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
        ]);
    }
}