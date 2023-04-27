<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class smsService
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $address;
    public $date;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param $address
     * @param $date
     */
    public function __construct(User $user, $address , $date)
    {
        $this->user = $user;
        $this->address = $address;
        $this->date = $date;
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
