<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;
class ContactCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($item) {
                return [
                    'sort_by' => $item->created_at,
                    'id' => $item->id,
                    'name'=>$item->name,
                    'phone'=>$item->phone,
                    'text'=>Str::limit($item->text, 40, ' ...'),
                    'card' => isset($item->card) ? $item->card->title :'ندارد',
                    'created_at' => verta($item->created_at)->format('Y/m/d H:i:s'),
                    'status'=>$item->status,
                ];
            })
        ];
    }
}
