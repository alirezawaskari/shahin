<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CenterCollection extends ResourceCollection
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
                    'items' => implode(' | ',$item->services->pluck('title')->toArray()),
                    'creator_rpresentation' => $item->creatorRpresentation ? $item->creatorRpresentation->fa_name : 'نمایندگی کل',
                    'created_at' => verta($item->created_at)->format('Y/m/d H:m:s'),
                    'status' => $item->status,
                    'services' => $item->services,
                ];
            })
        ];
    }
}


