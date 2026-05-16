<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class IncomingCallEvent implements ShouldBroadcast
{
    public int $callId;
    public int $callerId;
    public int $receiverId;
    public string $callerName;
    public string $channel;

    public function __construct(
        int $callId,
        int $callerId,
        int $receiverId,
        string $callerName,
        string $channel
    ) {
        $this->callId = $callId;
        $this->callerId = $callerId;
        $this->receiverId = $receiverId;
        $this->callerName = $callerName;
        $this->channel = $channel;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('call-channel.' . $this->receiverId);
    }

    public function broadcastAs()
    {
        return 'incoming_call';
    }
}