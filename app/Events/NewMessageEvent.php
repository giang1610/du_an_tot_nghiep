<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class NewMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $message;
    public $userId;

    public function __construct($message, $userId)
    {
        $this->message = $message;
        $this->userId = $userId;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('chat.' . $this->userId); 
    }

    public function broadcastAs(): string
    {
        return 'chat.message'; 
    }
    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'user_id' => $this->userId, 
            'id' => now()->timestamp, 
        ];
    }

}

