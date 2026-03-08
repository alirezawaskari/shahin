<?php

namespace App\Events;

use App\Models\Callcenter;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
     * Summary of __construct
     * @param User $user
     * @param mixed $service
     * @param Callcenter $task
     * @param mixed $type
     */
    public function __construct(User $user  , $service ,Callcenter $task,$type)
    {
        $this->user = $user;
        $this->service = $service;
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
