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
            // 4 voucher giảm giá sản phẩm
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
                'name' => 'Giảm 50.000 cho đơn hàng từ 500k',
                'code' => '50KOFF',
                'type' => 'product',
                'discount_type' => 'amount',
                'discount_amount' => 50000,
                'discount_percent' => null,
                'start_date' => '2025-07-01',
                'end_date' => '2025-12-31',
                'quantity' => 50,
                'usage_limit' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Giảm 15% cho đơn hàng nữ',
                'code' => 'NU15',
                'type' => 'product',
                'discount_type' => 'percent',
                'discount_amount' => null,
                'discount_percent' => 15,
                'start_date' => '2025-07-01',
                'end_date' => '2025-12-31',
                'quantity' => 80,
                'usage_limit' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Giảm 100.000 cho đơn hàng từ 1 triệu',
                'code' => '100KOFF',
                'type' => 'product',
                'discount_type' => 'amount',
                'discount_amount' => 100000,
                'discount_percent' => null,
                'start_date' => '2025-07-01',
                'end_date' => '2025-12-31',
                'quantity' => 30,
                'usage_limit' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // 5 voucher giảm phí ship
            [
                'name' => 'Giảm 10.000 cho ship',
                'code' => 'SHIP10K',
                'type' => 'shipping',
                'discount_type' => 'amount',
                'discount_amount' => 10000,
                'discount_percent' => null,
                'start_date' => '2025-08-01',
                'end_date' => '2025-12-31',
                'quantity' => 50,
                'usage_limit' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Giảm 20.000 cho ship',
                'code' => 'SHIP20K',
                'type' => 'shipping',
                'discount_type' => 'amount',
                'discount_amount' => 20000,
                'discount_percent' => null,
                'start_date' => '2025-07-01',
                'end_date' => '2025-12-31',
                'quantity' => 100,
                'usage_limit' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Giảm 50% cho ship',
                'code' => 'SHIP50P',
                'type' => 'shipping',
                'discount_type' => 'percent',
                'discount_amount' => null,
                'discount_percent' => 50,
                'start_date' => '2025-07-01',
                'end_date' => '2025-12-31',
                'quantity' => 60,
                'usage_limit' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Miễn phí ship cho đơn từ 200k',
                'code' => 'FREESHIP200',
                'type' => 'shipping',
                'discount_type' => 'amount',
                'discount_amount' => 20000,
                'min_order_amount' => 200000,
                'discount_percent' => null,
                'start_date' => '2025-07-01',
                'end_date' => '2025-12-31',
                'quantity' => 40,
                'usage_limit' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Giảm 30.000 cho ship',
                'code' => 'SHIP30K',
                'type' => 'shipping',
                'discount_type' => 'amount',
                'discount_amount' => 30000,
                'discount_percent' => null,
                'start_date' => '2025-07-01',
                'end_date' => '2025-12-31',
                'quantity' => 80,
                'usage_limit' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
