<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class oderStatus implements ShouldBroadcast
{
    use SerializesModels ,InteractsWithSockets, Dispatchable;

    public string $orderNumber;
    public int $id;
    public string $status;

    public function __construct( string $orderNumber,int $id, string $status)
    {  
        $this->orderNumber = $orderNumber;
        $this->id = $id;
        $this->status = $status;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('admin_status'); 
    }

    public function broadcastAs(): string
    {
        return 'order.status';
    }

    public function broadcastWith(): array
    {
        return [
            'order_number' => $this->orderNumber,
            'id' => $this->id,
            'status' => $this->status
        ];
    }
}
