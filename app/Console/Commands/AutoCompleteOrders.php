<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;

class AutoCompleteOrders extends Command
{
    protected $signature = 'orders:auto-complete';
    protected $description = 'Tự động chuyển đơn hàng Đã nhận sang Đã hoàn thành sau 3 ngày';

    public function handle()
    {
        $orders = Order::where('status', 'delivered')
            ->where('delivered_at', '<=', Carbon::now()->subDays(3))
            ->whereNull('return_requested_at')
            ->get();

        foreach ($orders as $order) {
            $order->status = 'completed';
            $order->completed_at = now();
            $order->save();
        }

        $this->info('Đã tự động hoàn thành ' . $orders->count() . ' đơn hàng.');
    }
}
