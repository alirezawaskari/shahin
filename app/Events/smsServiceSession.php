<?php

namespace App\Events;

use App\Models\FactorItem;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class smsServiceSession
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $factorItem;
    public $date;

    /**
     * Summary of __construct
     * @param FactorItem $factorItem
     * @param mixed $date
     */
    public function __construct(FactorItem $factorItem , $date)
    {
        $this->factorItem = $factorItem;
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
