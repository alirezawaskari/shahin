<?php

namespace App\Http\Resources;

use App\Models\Ticket;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TicketCollection extends ResourceCollection
{

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($item) {
                return [
                    'sort_by' => "created_at",
                    'id' => $item->id,
                    'title'=>$item->title,
                    'sender'=>[
                        'id'=> $item->user ? $item->user->id : 'ثبت نشده',
                        'name'=> $item->user ? $item->user->name : 'ثبت نشده',
                        'family'=>$item->user ? $item->user->family : 'ثبت نشده'
                    ],
                    'status'=>$item->status,
                    'receivers'=>Ticket::setReceiver($item),
                    'created_at' => verta($item->created_at)->format('Y/m/d H:i:s'),
                ];
            })
        ];
    }
}
