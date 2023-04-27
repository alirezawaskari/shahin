<?php

namespace App\Events;

use App\Models\FactorItem;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class factorItemStartWorking
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $pass;
    public $factorItem;
    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param $pass
     * @param FactorItem $factorItem
     */
    public function __construct(User $user , FactorItem $factorItem , $pass)
    {
        $this->user = $user;
        $this->factorItem = $factorItem;
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
