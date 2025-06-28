<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class OrderConfirmation extends Notification
{
    use Queueable;

    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Xác nhận đơn hàng')
            ->line('Cảm ơn bạn đã đặt hàng.')
            ->line('Mã đơn hàng: #' . $this->order->id)
            ->action('Xem chi tiết đơn hàng', url('/orders/' . $this->order->id))
            ->line('Cảm ơn bạn đã mua sắm cùng chúng tôi!');
    }
}
