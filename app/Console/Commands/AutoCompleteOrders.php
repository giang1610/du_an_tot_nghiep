<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;

class AutoCompleteOrders extends Command
{
    protected $signature = 'orders:auto-complete';
    protected $description = 'Tự động chuyển đơn hàng Đã nhận hoặc Đã hoàn hàng sang Đã hoàn thành sau 3 ngày';

    public function handle()
    {
        // Đơn hàng đã giao (shipped) > 3 ngày, chưa có yêu cầu hoàn hàng
        $completedOr = Order::where('status', 'shipped')
            ->whereNotNull('shipped_at')
            ->where('shipped_at', '<=', Carbon::now()->subDays(3))
            ->whereNotIn('status', ['return_requested', 'returned'])
            ->get();

        // Đơn hàng returned > 3 ngày
        $returnedOrders = Order::where('status', 'returned')
            ->whereNotNull('returned_at')
            ->where('returned_at', '<=', Carbon::now()->subDays(3))
            ->get();

        $count = 0;

        foreach ($completedOr as $order) {
            $order->status = 'completed';
            $order->completed_at = now();
            $order->save();
            $count++;
        }

        foreach ($returnedOrders as $order) {
            $order->status = 'completed';
            $order->completed_at = now();
            $order->save();
            $count++;
        }

        $this->info('Đã tự động hoàn thành ' . $count . ' đơn hàng.');
    }
}
