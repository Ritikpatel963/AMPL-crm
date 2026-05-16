<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ReverbTestEvent implements ShouldBroadcastNow
{
    public function broadcastOn()
    {
        return new PrivateChannel('reverb.test');
    }

    public function broadcastAs()
    {
        return 'reverb.tested';
    }

    public function broadcastWith()
    {
        return ['ok' => true];
    }
}