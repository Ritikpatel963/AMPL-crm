<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSendEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($message)
    {
        // Load sender relation once
        $this->message = $message->load('sender:id,name');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat-channel.' . $this->message->receiver_id),
        ];
    }

    /**
     * Custom event name for mobile/web
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Clean, stable payload
     */
    public function broadcastWith(): array
    {
        return [
            'id'          => $this->message->id,
            'sender_id'   => $this->message->sender_id,
            'receiver_id' => $this->message->receiver_id,
            'type'        => $this->message->type,
            'message'     => $this->message->message,
            'data'        => $this->message->data,
            'sender'      => $this->message->sender,
            'created_at_formatted'  => $this->message->created_at->toDateTimeString(),
        ];
    }
}