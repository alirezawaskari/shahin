<?php

namespace App\Events;

use App\Models\Center;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class referralCenterNotification
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $center;

    /**
     * Create a new event instance.
     *
     * @param Center $center
     */
    public function __construct(Center $center)
    {
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
