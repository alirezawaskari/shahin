<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class fastregisterCallcenter
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $pass;
    /**
     * Create a new event instance.
     *
     * @return void
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
