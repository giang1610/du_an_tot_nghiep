<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastnow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class NewMessageEvent implements ShouldBroadcastnow
{
    use Dispatchable, SerializesModels;

    public $message;
    public $userId;
    public $name;
    public $sender;
    public $avatar;


    

    public function __construct($message, $userId,$name,$sender, $avatar)
    {
        $this->message = $message;
        $this->userId = $userId;
        $this->name = $name;
        $this->sender = $sender;
        $this->avatar = $avatar; 
    }

    public function broadcastOn(): array   
    {
        return [
            new Channel('chat.admin'),
            new Channel('chat.' . $this->userId)
        ];
        
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
            'name' => $this->name,
            'sender' => $this->sender,
            'avatar' => $this->avatar,
            'id' => now()->timestamp, 
        ];
    }

}

