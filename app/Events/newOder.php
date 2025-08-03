<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class newOder implements ShouldBroadcast
{
    use  InteractsWithSockets, SerializesModels;
   
    public Order $order;

    public function __construct( Order $order)
    {
        $this->order = $order;
    }
    
    public function broadcastOn(): Channel
    {
        return new Channel('orders'); 
    }

    public function broadcastAs(): string
    {
        return 'order.status';
    }

    public function broadcastWith(): array
    {
        return [
           'data' => $this->order,
           'message' => 'New order has been created',
        ];
    }
}
