<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OrganizationCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($item) {
                return [
                    'id' => $item->id,
                    'archive_number' => $item->archive_number,
                    'name' => $item->fa_name,
                    'parent_id' => $item->parent_id,
                    'slug' => $item->slug,
                    'type' =>  $item::TYPE[$item->type],
                    'created_at' => verta($item->created_at)->format('Y/m/d'),
                    'archive_status' => $item::status($item->archive_status),
                    'children_count' => $item->children->count(),
                    'archive_status_class' => $item::getClassByStatus($item->archive_status),
                ];
            })
        ];
    }
}
