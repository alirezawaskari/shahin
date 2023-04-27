<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class factorItemActivation
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $center;
    public $user;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param $pass
     */
    public function __construct(User $user , $center)
    {
        $this->user = $user;
        $this->center = $center;
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
