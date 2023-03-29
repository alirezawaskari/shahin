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

class fastserviceCallcenter
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $service;
    public $factor;
    public $callcenter;
    public $type;
    public $task;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param $service
     * @param Factor $factor
     * @param $callcenter
     */
    public function __construct(User $user  , $service , Factor $factor = null,Callcenter $task,$type)
    {
        $this->user = $user;
        $this->service = $service;
        $this->factor = $factor;
        $this->callcenter =$task->callcenter;
        $this->type = $type;
        $this->task = $task;
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
