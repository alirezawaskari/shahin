<?php

namespace App\Events;

use App\Models\FactorItem;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class notificationRecordingFactor{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $factorItem;
    /**
     * Create a new event instance.
     *
     * @param FactorItem $factorItem
     */
    public function __construct(FactorItem $factorItem)
    {
        $this->factorItem = $factorItem;
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
