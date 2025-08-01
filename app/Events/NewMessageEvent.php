<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class NewMessageEvent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $message;
    public $userId;
    public $sender;
    public $avatar;


    

    public function __construct($message, $userId,$sender, $avatar)
    {
        $this->message = $message;
        $this->userId = $userId;
        $this->sender = $sender;
        $this->avatar = $avatar; 
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
            'sender' => $this->sender,
            'avatar' => $this->avatar,
            'id' => now()->timestamp, 
        ];
    }

}

