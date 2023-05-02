<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ShortLinkCollection extends ResourceCollection
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
                    'sort_by' => "updated_at",
                    'id' => $item->id,
                    'original_link' => $item->original_link,
                    'short_link' => $item->short_link,
                    'created_at' => verta($item->created_at)->format('%d %B ، %Y - H:i:s') ,

                ];
            })
        ];
    }

}
