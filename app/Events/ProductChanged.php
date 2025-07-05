<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ProductChanged implements ShouldBroadcast
{
    use SerializesModels;

    public function broadcastOn()
    {
        return new Channel('products'); 
    }

    public function broadcastAs()
    {
        return 'product.changed'; 
    }

    public function broadcastWith()
    {
        return []; 
    }
}
