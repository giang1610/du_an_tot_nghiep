<?php

namespace App\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Mail\OrderStatusUpdated;
use Illuminate\Support\Facades\Mail;



class UpdateOrderStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
      protected $orderId;

    /**
     * Create a new job instance.
     */
    public function __construct( $orderId)
    {
         $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     */
   public function handle(): void
{
     \Log::info('Job chạy với orderId: ' . $this->orderId);
    $order = Order::find($this->orderId);
    if ($order && $order->user && $order->user->email) {
        switch ($order->status) {
            case 'shipping':
                Mail::to($order->user->email)->queue(new \App\Mail\OrderGiao($order));

                break;
            case 'cancelled':
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
                // Nếu muốn, có thể gửi mail mặc định hoặc không gửi gì
                break;
        }
        // tạo sự kiện realTime trạng thái
        broadcast(new \App\Events\UpdateStatus($order->id, $order->status))->toOthers();

    }
}
}
