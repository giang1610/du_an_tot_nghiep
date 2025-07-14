<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class UserTypingEvent implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $user;

    public function __construct($user)
    {
        $this->user = [
            'id' => $user->id,
            'name' => $user->name
        ];
    }

    public function broadcastOn()
    {
        return new Channel('chat-typing');
    }

    public function broadcastAs()
    {
        return 'user.typing';
    }
}
