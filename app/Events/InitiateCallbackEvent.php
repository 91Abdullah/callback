<?php

namespace App\Events;

use Clue\React\Ami\ActionSender;
use Clue\React\Ami\Client;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class InitiateCallbackEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $uniqueid;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($uniqueid)
    {
        $this->uniqueid = $uniqueid;
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
