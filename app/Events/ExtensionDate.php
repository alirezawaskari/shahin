<?php

namespace App\Events;


use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ExtensionDate
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $phone;
    public $item;


    /**
     * @param $phone
     * @param $item
     */
    public function __construct($phone, $item)
    {
        $this->phone = $phone;
        $this->item = $item;

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
