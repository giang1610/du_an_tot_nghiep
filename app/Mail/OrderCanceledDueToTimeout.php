<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderCanceledDueToTimeout extends Mailable
{
    use Queueable, SerializesModels;
    
    public $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function build()
    {
        return $this->subject('Đơn hàng #' . $this->order->id . ' đã bị hủy do quá hạn thanh toán')
                    ->view('emails.orders.timeout_cancel');
    }
}
