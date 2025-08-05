<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class oderStatus
{
    use SerializesModels;

    public string $orderNumber;
    public int $id;

    public function __construct( string $orderNumber,int $id)
    {  
        $this->id = $id;
        $this->orderNumber = $orderNumber;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('admin-orders'); 
    }

    public function broadcastAs(): string
    {
        return 'order.fail';
    }

    public function broadcastWith(): array
    {
        return [
            'order_number' => $this->orderNumber,
            'id' => $this->id,
        ];
    }
}
