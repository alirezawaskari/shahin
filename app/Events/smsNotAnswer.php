<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class smsNotAnswer
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $callcenter;
    public $unit;

    /**
     * Create a new event instance.
     * @param User $user
     * @param $callcenter
     * @return void
     */
    public function __construct(User $user , $callcenter , $unit)
    {
        $this->user = $user;
        $this->callcenter = $callcenter;
        $this->unit = $unit;
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
