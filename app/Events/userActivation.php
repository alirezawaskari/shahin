<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class userActivation
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pass;
    public $user;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param $pass
     */
    public function __construct(User $user , $pass)
    {
        $this->user = $user;
        $this->pass = $pass;
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
