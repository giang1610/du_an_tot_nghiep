<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class newOder implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->load(
            'user',
            'items.productVariant.product',
            'items.size',
            'items.color'
        );
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
        $item = $this->order->items->first();

        return [
            'data' => $this->order->toArray(),
            'product_name' => optional($item->productVariant->product)->name ?? 'Không rõ',
            'quantity'     => $item->quantity ?? 0,
            'size'         => optional($item->size)->name ?? null,
            'color'        => optional($item->color)->name ?? null,
            'message'      => 'New order has been created',
        ];
    }
}
