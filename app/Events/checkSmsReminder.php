<?php

namespace App\Events;

 use App\Models\Check;
 use App\Models\User;
 use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
 use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class checkSmsReminder
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $check;



    /**
     * Create a new event instance.

     */
    public function __construct(User $user  , Check $check  )
    {
        $this->user = $user;
        $this->check = $check;
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
