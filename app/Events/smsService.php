<?php

namespace App\Events;

use App\Models\FactorItem;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class smsService
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $factorItem;
    public $type;

    /**
     * Summary of __construct
     * @param FactorItem $factorItem
     * @param string $type
     */
    public function __construct(FactorItem $factorItem , $type)
    {
        $this->factorItem = $factorItem;
        $this->type = $type;
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
