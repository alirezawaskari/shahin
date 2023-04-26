<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class NewsCollection extends ResourceCollection
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
                    'title' => $item->title,
                    'image' => $item->image,
                    'subject' => $item->subject,
                    'user' => $item->user,
                    'status' => $item->status,
                    'created_at' => $item->created_at
                ];
            })
        ];
    }
}
