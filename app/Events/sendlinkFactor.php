<?php

namespace App\Events;

use App\Models\Factor;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
