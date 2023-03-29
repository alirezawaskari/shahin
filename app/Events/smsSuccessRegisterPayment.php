<?php

namespace App\Events;

use App\Models\Factor;
use App\Models\FactorItem;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class smsSuccessRegisterPayment
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $factor;
    public $supporter_user;


    public function __construct( Factor $factor, User $supporter_user)
    {
        $this->factor = $factor;
        $this->supporter_user = $supporter_user;
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
