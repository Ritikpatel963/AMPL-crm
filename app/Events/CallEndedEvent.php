<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Call;

class CallEndedEvent implements ShouldBroadcast
{
    public int $callId;
    public string $channel;
    public int $callerId;
    public int $receiverId;

    public function __construct(int $callId)
    {
        $call = Call::findOrFail($callId);

        $this->callId     = $call->id;
        $this->channel    = $call->channel_name;
        $this->callerId   = $call->caller_id;
        $this->receiverId = $call->receiver_id;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('call-channel.' . $this->callerId),
            new PrivateChannel('call-channel.' . $this->receiverId),
        ];
    }

    public function broadcastAs()
    {
        return 'call_ended';
    }
}