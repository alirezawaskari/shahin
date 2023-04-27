<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class successFactor
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $resnumber;

    /**
     * Create a new event instance.
     *
     * @param User $user
     */
    public function __construct(User $user, $resnumber)
    {
        $this->user = $user;
        $this->resnumber = $resnumber;
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
