<?php

namespace App\Events;

use App\Models\Callcenter;
use App\Models\Factor;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class sendlinkFactor
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $factor;
    public $callcenter;
    public $type;


    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param $service
     * @param Factor $factor
     * @param $callcenter
     */
    public function __construct(User $user  , Factor $factor , $callcenter, $type)
    {
        $this->user = $user;
        $this->factor = $factor;
        $this->callcenter = $callcenter;
        $this->type = $type;
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
