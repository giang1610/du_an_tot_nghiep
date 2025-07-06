<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;

class ProductStockUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $variantId;
    public $stock;

    public function __construct($variantId, $stock)
    {
        $this->variantId = $variantId;
        $this->stock = $stock;
    }

    public function broadcastOn()
    {
        return new Channel('product-stock');
    }

    public function broadcastAs()
    {
        return 'stock.updated';
    }

    public function broadcastWith()
    {
        return [
            'variantId' => $this->variantId,
            'stock' => $this->stock,
        ];
    }
    
}
