<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DiseaseCollection extends ResourceCollection
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
                    'sort_by' => "created_at_georgian",
                    'id' => $item->id,
                    'name' => $item->name,
                    'created_at' => verta($item->created_at)->format('Y/m/d H:m:s'),
                    'created_at_georgian' =>  $item->created_at
                ];
            })
        ];
    }
}


