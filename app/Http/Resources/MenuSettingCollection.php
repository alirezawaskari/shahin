<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;


class MenuSettingCollection extends ResourceCollection
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
                    'name' => $item->name,
                    'icon' => $item->icon,
                    'position' => $item->position,
                    'updated_at' => verta($item->updated_at)->format('Y/m/d'),
                ];
            }),
        ];
    }

}

