<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateStatus implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $orderId;
    public $newStatus;
    public $paymentStatus;

    public function __construct($orderId, $newStatus,$paymentStatus)
    {
        $this->orderId = $orderId;
        $this->newStatus = $newStatus;
        $this->paymentStatus = $paymentStatus;
    }

    public function broadcastOn()
    {
        return new Channel('order-status');
    }

    public function broadcastAs()
    {
        return 'order.updated';
    }
}
