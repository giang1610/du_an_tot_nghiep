<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class UpdateOrderStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderId;

    /**
     * Create a new job instance.
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info('Job chạy với orderId: ' . $this->orderId);

        // Đảm bảo lấy đúng 1 order và load quan hệ user
        $order = Order::with('user')->find($this->orderId);

        // Kiểm tra user là object và có email
        if ($order && $order->user && !empty($order->user->email)) {
            switch ($order->status) {
                case 'shipping':
                    Mail::to($order->user->email)->queue(new \App\Mail\OrderGiao($order));
                    break;
                case 'errors':
                    Mail::to($order->user->email)->queue(new \App\Mail\OrderErrors($order));
                    break;
                case 'picking':
                    Mail::to($order->user->email)->queue(new \App\Mail\OrderPicking($order));
                    break;
                case 'processing':
                    Mail::to($order->user->email)->queue(new \App\Mail\OrderProcessing($order));
                    break;
                case 'shipped':
                    Mail::to($order->user->email)->queue(new \App\Mail\OrderShipped($order));
                    break;
                default:
                    // Không gửi mail nếu không khớp trạng thái
                    break;
            }
        }
    }
}
