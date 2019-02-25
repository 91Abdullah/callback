<?php

namespace App\Events;

use App\AbandonedCall;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CallerAbandonEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $uniqueId;
    public $abandon;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(AbandonedCall $abandon, $uniqueId)
    {
        $this->uniqueId = $uniqueId;
        $this->abandon = $abandon;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
