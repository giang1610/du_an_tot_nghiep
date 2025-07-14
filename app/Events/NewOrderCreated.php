<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class NewOrderCreated implements ShouldBroadcast
{
    use SerializesModels;

    public string $orderNumber;
    public int $id;

    public function __construct(string $orderNumber, int $id)
    {
        $this->orderNumber = $orderNumber;
        $this->id = $id;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('admin-orders'); 
    }

    public function broadcastAs(): string
    {
        return 'order.created';
    }

    public function broadcastWith(): array
    {
        return [
            'order_number' => $this->orderNumber,
            'id' => $this->id,
        ];
    }
}
