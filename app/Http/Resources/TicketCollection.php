<?php

namespace App\Http\Resources;

use App\Models\Ticket;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TicketCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($item) {
                return [
                    'sort_by' => "created_at",
                    'id' => $item->id,
                    'title'=>$item->title,
                    'sender'=>[
                        'id'=>$item->user->id,
                        'name'=>$item->user->name,
                        'family'=>$item->user->family
                    ],
                    'status'=>$item->status,
                    'receivers'=>Ticket::setReceiver($item),
                    'created_at' => verta($item->created_at)->format('Y/m/d H:i:s'),
                ];
            })
        ];
    }
}
